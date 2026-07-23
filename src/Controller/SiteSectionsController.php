<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class SiteSectionsController extends AppController
{
    public function edit(int $siteId): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');
        $site = $this->fetchTable('Sites')->find()
            ->contain(['SiteSections'])
            ->where(['Sites.id' => $siteId, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $sectionsTable = $this->fetchTable('SiteSections');
            $sections = $sectionsTable->patchEntities(
                $site->site_sections,
                (array)$this->request->getData('sections'),
            );

            if ($sectionsTable->saveMany($sections)) {
                $this->Flash->success('Secciones actualizadas.');

                return $this->redirect(['controller' => 'Sites', 'action' => 'edit', $siteId]);
            }

            $this->Flash->error('No pudimos guardar las secciones.');
        }

        $this->set(compact('site'));

        return null;
    }

    public function toggle(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $sections = $this->fetchTable('SiteSections');
        $section = $sections->find()
            ->contain(['Sites'])
            ->where(['SiteSections.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        $section->visible = !$section->visible;
        $sections->saveOrFail($section);

        return $this->redirect(['controller' => 'Sites', 'action' => 'edit', $section->site_id]);
    }
}
