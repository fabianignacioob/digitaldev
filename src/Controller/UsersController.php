<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;

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
                        'plan' => 'basica',
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

            $session->delete('PendingVerification');
            $session->write('Auth.User', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            $this->Flash->success('Correo verificado. Elige un plan para activar tu primer sitio.');

            return $this->redirect('/planes');
        }

        $this->set(compact('email'));

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
        if ($redirect = $this->requireLogin()) {
            return $redirect;
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

        return $this->planService()->getPlanBySlug($plan) ? $plan : 'basica';
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
            $mailer = new Mailer('default');
            $mailer
                ->setTo((string)$user->email, (string)$user->name)
                ->setSubject('Código de verificación CatOps')
                ->setEmailFormat('text')
                ->setViewVars(['user' => $user, 'code' => $code])
                ->viewBuilder()
                ->setTemplate('verification_code');
            $mailer->deliver();
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar el código de verificación: ' . $exception->getMessage());
            if (Configure::read('debug')) {
                $this->Flash->warning('Modo local: no hay SMTP activo. Código de prueba: ' . $code);
            }
        }
    }

}
