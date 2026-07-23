<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class SubscriptionsController extends AppController
{
    public function extendMonthly(): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        return $this->manualPaymentDisabled();
    }

    public function payAnnual(): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        return $this->manualPaymentDisabled();
    }

    public function upgrade(string $plan): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        return $this->manualPaymentDisabled();
    }

    private function manualPaymentDisabled(): Response
    {
        $this->Flash->warning('Los pagos manuales ya no están disponibles para clientes. Completa el pago mediante Webpay.');

        return $this->redirect('/planes');
    }
}
