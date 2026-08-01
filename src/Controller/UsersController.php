<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\EmailService;
use App\Service\SubscriptionService;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Routing\Router;
use RuntimeException;
use Throwable;

class UsersController extends AppController
{
    public function register(): ?Response
    {
        $this->viewBuilder()->setLayout('auth');
        $users = $this->fetchTable('Users');
        $user = $users->newEmptyEntity();
        $selectedPlan = $this->normalizePlan($this->request->getQuery('plan'));

        if ($this->request->is('post')) {
            $user = $users->patchEntity($user, $this->request->getData() + [
                'role' => 'user',
                'active' => false,
                'email_verified' => false,
            ]);
            $code = $this->setVerificationCode($user);

            if ($users->save($user)) {
                $this->request->getSession()->write('PendingVerification', [
                    'user_id' => $user->id,
                    'plan' => $selectedPlan,
                ]);
                $this->sendVerificationEmail($user, $code);
                $this->Flash->success('Cuenta creada. Te enviamos un código de verificación al correo.');

                return $this->redirect(['action' => 'verifyEmail']);
            }

            $this->Flash->error('No pudimos crear la cuenta. Revisa los datos.');
        }

        $this->set(compact('user', 'selectedPlan'));

        return null;
    }

    public function login(): ?Response
    {
        $this->viewBuilder()->setLayout('auth');

        if ($this->request->is('post')) {
            $users = $this->fetchTable('Users');
            $user = $users->find()
                ->where([
                    'email' => $this->request->getData('email'),
                ])
                ->first();

            if ($user && password_verify((string)$this->request->getData('password'), (string)$user->password)) {
                if (!$user->email_verified) {
                    $this->request->getSession()->write('PendingVerification', [
                        'user_id' => $user->id,
                        'plan' => $this->defaultRegistrationPlan(),
                    ]);
                    $this->Flash->warning('Antes de entrar debes verificar tu correo.');

                    return $this->redirect(['action' => 'verifyEmail']);
                }

                if (!$user->active) {
                    $this->Flash->error('Tu cuenta está pausada. Escríbenos para revisar el acceso.');

                    return null;
                }

                $this->request->getSession()->write('Auth.User', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]);

                return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
            }

            $this->Flash->error('Correo o contraseña incorrectos.');
        }

        return null;
    }

    public function verifyEmail(): ?Response
    {
        $this->viewBuilder()->setLayout('auth');
        $session = $this->request->getSession();
        $pending = (array)$session->read('PendingVerification');
        $email = '';

        if (!empty($pending['user_id'])) {
            $user = $this->fetchTable('Users')->find()
                ->where(['id' => (int)$pending['user_id']])
                ->first();
            $email = (string)($user->email ?? '');
        }

        if ($this->request->is('post')) {
            $email = (string)$this->request->getData('email');
            $code = preg_replace('/\D+/', '', (string)$this->request->getData('code'));
            $user = $this->fetchTable('Users')->find()
                ->where(['email' => $email])
                ->first();

            if (!$user || !$code || !$this->verificationCodeIsValid($user, $code)) {
                $this->Flash->error('Código inválido o vencido.');
                $this->set(compact('email'));

                return null;
            }

            $user->email_verified = true;
            $user->active = true;
            $user->verification_code_hash = null;
            $user->verification_expires = null;
            $user->verification_sent_at = null;
            $this->fetchTable('Users')->saveOrFail($user);

            $selectedPlan = (string)($pending['plan'] ?? '');
            $trialCreated = false;
            if ($selectedPlan !== '' && ($plan = $this->planService()->getPlanBySlug($selectedPlan)) && $this->planService()->isTrialPlan($plan)) {
                try {
                    (new SubscriptionService())->createTrialForUser((int)$user->id);
                    $trialCreated = true;
                } catch (RuntimeException $exception) {
                    $this->Flash->warning($exception->getMessage());
                }
            }

            $session->delete('PendingVerification');
            $session->write('Auth.User', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
            $welcomePlan = $selectedPlan !== '' ? $this->planService()->getPlanBySlug($selectedPlan) : null;
            $this->sendWelcomeEmail($user, $welcomePlan);

            $this->Flash->success($trialCreated
                ? 'Correo verificado. Puedes crear tu sitio; la prueba de 7 días comienza al publicarlo.'
                : 'Correo verificado. Elige un plan para activar tu primer sitio.');

            if ($trialCreated) {
                return $this->redirect('/panel');
            }

            return $this->redirect('/planes?plan=' . rawurlencode($selectedPlan));
        }

        $this->set(compact('email'));

        return null;
    }

    public function forgotPassword(): ?Response
    {
        $this->viewBuilder()->setLayout('auth');

        if ($this->request->is('post')) {
            $email = trim((string)$this->request->getData('email'));
            $user = $this->fetchTable('Users')->find()
                ->where(['email' => $email])
                ->first();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->password_reset_token_hash = hash('sha256', $token);
                $user->password_reset_expires = DateTime::now()->addMinutes(30);
                $user->password_reset_requested_at = DateTime::now();
                $this->fetchTable('Users')->saveOrFail($user);

                $resetUrl = Router::url([
                    'controller' => 'Users',
                    'action' => 'resetPassword',
                    '?' => ['token' => $token],
                ], true);

                try {
                    (new EmailService())->sendPasswordReset($user, $resetUrl);
                } catch (Throwable $exception) {
                    Log::warning('No se pudo enviar el correo de recuperación: ' . $exception->getMessage());
                }
            }

            // The response is intentionally generic to avoid revealing registered emails.
            $this->Flash->success('Si el correo está registrado, recibirás un enlace para recuperar tu contraseña.');

            return $this->redirect(['action' => 'login']);
        }

        return null;
    }

    public function resetPassword(): ?Response
    {
        $this->viewBuilder()->setLayout('auth');
        $token = trim((string)($this->request->getQuery('token') ?? $this->request->getData('token')));
        $validToken = $this->passwordResetUser($token);

        if (!$validToken) {
            $this->Flash->error('El enlace de recuperación es inválido o ya venció.');

            return $this->redirect(['action' => 'forgotPassword']);
        }

        if ($this->request->is('post')) {
            $password = (string)$this->request->getData('password');
            $confirmation = (string)$this->request->getData('password_confirmation');
            if (strlen($password) < 8) {
                $this->Flash->error('La contraseña debe tener al menos 8 caracteres.');
            } elseif ($password !== $confirmation) {
                $this->Flash->error('Las contraseñas no coinciden.');
            } else {
                $user = $validToken;
                $user->password = $password;
                $user->password_reset_token_hash = null;
                $user->password_reset_expires = null;
                $user->password_reset_requested_at = null;
                $this->fetchTable('Users')->saveOrFail($user);
                $this->Flash->success('Contraseña actualizada. Ya puedes iniciar sesión.');

                return $this->redirect(['action' => 'login']);
            }
        }

        $this->set(compact('token'));

        return null;
    }

    public function resendCode(): Response
    {
        $this->request->allowMethod(['post']);
        $pending = (array)$this->request->getSession()->read('PendingVerification');
        if (empty($pending['user_id'])) {
            $this->Flash->error('No encontramos una verificación pendiente.');

            return $this->redirect(['action' => 'register']);
        }

        $users = $this->fetchTable('Users');
        $user = $users->get((int)$pending['user_id']);
        $code = $this->setVerificationCode($user);
        $users->saveOrFail($user);
        $this->sendVerificationEmail($user, $code);
        $this->Flash->success('Te enviamos un nuevo código.');

        return $this->redirect(['action' => 'verifyEmail']);
    }

    public function activatePlan(string $plan): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $selectedPlan = $this->planService()->getPlanBySlug($plan);
        if ($selectedPlan && $this->planService()->isTrialPlan($selectedPlan)) {
            try {
                (new SubscriptionService())->createTrialForUser((int)$this->currentUserId());
                $this->Flash->success('Prueba gratuita activada. Crea y publica tu primer sitio para iniciar los 7 días.');

                return $this->redirect('/panel');
            } catch (\RuntimeException $exception) {
                $this->Flash->warning($exception->getMessage());

                return $this->redirect('/planes');
            }
        }

        $this->Flash->warning('La activación se completa después de confirmar el pago.');

        return $this->redirect('/planes');
    }

    public function logout(): Response
    {
        $this->request->getSession()->delete('Auth.User');
        $this->Flash->success('Sesión cerrada.');

        return $this->redirect('/');
    }

    private function normalizePlan(mixed $plan): string
    {
        $plan = strtolower((string)$plan);

        if ($plan === 'basico') {
            $plan = 'basica';
        }
        if ($plan === 'completo') {
            $plan = 'full';
        }

        return $this->planService()->getPlanBySlug($plan) ? $plan : $this->defaultRegistrationPlan();
    }

    private function defaultRegistrationPlan(): string
    {
        return (string)($this->planService()->trialPlan()?->slug ?? 'basica');
    }

    private function setVerificationCode(object $user): string
    {
        $code = (string)random_int(100000, 999999);
        $user->verification_code_hash = password_hash($code, PASSWORD_DEFAULT);
        $user->verification_expires = DateTime::now()->addMinutes(15);
        $user->verification_sent_at = DateTime::now();

        return $code;
    }

    private function verificationCodeIsValid(object $user, string $code): bool
    {
        if (!$user->verification_code_hash || !$user->verification_expires) {
            return false;
        }

        if ($user->verification_expires < DateTime::now()) {
            return false;
        }

        return password_verify($code, (string)$user->verification_code_hash);
    }

    private function sendVerificationEmail(object $user, string $code): void
    {
        try {
            (new EmailService())->sendVerificationCode($user, $code);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el código de verificación: ' . $exception->getMessage());
            if (Configure::read('debug')) {
                $this->Flash->warning('Modo local: no hay SMTP activo. Código de prueba: ' . $code);
            }
        }
    }

    private function sendWelcomeEmail(object $user, ?object $plan = null): void
    {
        try {
            (new EmailService())->sendWelcome($user, $plan);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el correo de bienvenida: ' . $exception->getMessage());
        }
    }

    private function passwordResetUser(string $token): ?object
    {
        if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $user = $this->fetchTable('Users')->find()
            ->where(['password_reset_token_hash' => hash('sha256', $token)])
            ->first();
        if (!$user || !$user->password_reset_expires || $user->password_reset_expires < DateTime::now()) {
            return null;
        }

        return $user;
    }

}
