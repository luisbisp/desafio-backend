<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Respondent;
use App\Notifications\NotificationUser;
use Illuminate\Support\Facades\Http;

class FormNotificationService
{
    /**
     * Envia notificação para o criador do formulário.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return void
     */
    public function notifyFormCreatorEmail(Form $form, Respondent $respondent): void
    {
        $formCreator = $form->user;

        if ($form->notification['email']) {
            $formCreator->notify(new NotificationUser(
                $formCreator,
                [
                    'subject' => "Novo preenchimento no '{$form->title}'",
                    'title' => "Novo preenchimento no '{$form->title}' recebido. Confira no link:",
                    'message' => "https://teste.com/api/forms/{$respondent->form_id}",
                ]
            ));
        }
    }

    public function notifyFormCreatorWhatsapp(Form $form, Respondent $respondent)
    {
        $formCreator = $form->user;
        if ($form->notification['whatsapp'] && $formCreator->phone) {
            
            try {
                $response = Http::post(config('services.whatsapp.url'), [
                    'phone' => $formCreator->phone,
                    'message' => "Novo preenchimento no '{$form->title}' recebido. Confira no link: https://teste.com/api/forms/{$respondent->form_id}",
                    ]);

                if ($response->successful()) {
                    return [
                        'error' => false,
                        'response' =>$response->json()
                    ];
                } else {
                    return [
                        'error' => true,
                        'status' => $response->status(),
                        'message' => $response->body(),
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }
}
