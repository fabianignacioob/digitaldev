<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PublicUrlService;
use Cake\Http\Response;
use Cake\I18n\DateTime;

class DashboardController extends AppController
{
    public function index(): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');
        $sites = $this->fetchTable('Sites')->find()
            ->contain(['Templates', 'Themes', 'Domains'])
            ->where(['Sites.user_id' => $this->currentUserId()])
            ->orderByDesc('Sites.modified')
            ->all();
        $hasActivePlan = $this->hasActivePlan();
        $subscription = $this->currentSubscription();
        $plan = null;
        $daysRemaining = null;
        $upgradePlans = [];
        $planCapabilities = [];
        $planServiceCapabilities = [];
        $siteUsage = $this->planService()->siteUsage((int)$this->currentUserId());
        $publicUrlService = new PublicUrlService();
        $baseDomain = $publicUrlService->baseDomain();

        if ($subscription) {
            $plan = $this->planService()->getPlanBySlug((string)$subscription->plan_slug);
            $planCapabilities = $plan ? $this->planService()->capabilities($plan) : [];
            $plans = $this->fetchTable('Plans')->find()
                ->where(['active' => true])
                ->orderByAsc('sort_order')
                ->all();
            $planRanks = [];
            foreach ($plans as $candidate) {
                $planRanks[$candidate->slug] = (int)$candidate->sort_order;
            }

            $currentRank = $planRanks[$subscription->plan_slug] ?? 0;
            foreach ($plans as $candidate) {
                $planServiceCapabilities[$candidate->slug] = $this->planService()->capabilities($candidate);
                if ((int)$candidate->sort_order > $currentRank) {
                    $upgradePlans[] = $candidate;
                }
            }

            if ($subscription->ends_at) {
                $seconds = max(0, (new DateTime((string)$subscription->ends_at))->getTimestamp() - DateTime::now()->getTimestamp());
                $daysRemaining = (int)ceil($seconds / 86400);
            }
        }

        $this->set(compact(
            'sites',
            'hasActivePlan',
            'subscription',
            'plan',
            'daysRemaining',
            'upgradePlans',
            'siteUsage',
            'planCapabilities',
            'planServiceCapabilities',
            'publicUrlService',
            'baseDomain',
        ));

        return null;
    }
}
