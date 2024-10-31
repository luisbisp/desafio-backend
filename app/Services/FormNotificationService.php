<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Respondent;
use App\Notifications\NotificationUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FormNotificationService
{
    /**
     * Envia notificação para o criador do formulário(se ativada).
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

    /**
     * Envia notificação via WhatsApp(se ativada) para o criador do formulário.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return void
     */
    public function notifyFormCreatorWhatsapp(Form $form, Respondent $respondent): void
    {
        $formCreator = $form->user;

        if ($formCreator->plan === 'free' && $formCreator->whatsapp_msg_received_count >= 10) {
            $this->limitWhatsappMessage($form);
        }

        if ($form->notification['whatsapp'] && $formCreator->phone) {

            try {
                $response = Http::post(config('services.whatsapp.url'), [
                    'phone' => $formCreator->phone,
                    'message' => "Novo preenchimento no '{$form->title}' recebido. Confira no link: https://teste.com/api/forms/{$respondent->form_id}",
                ]);

                if ($response->successful()) {
                    $formCreator->increment('whatsapp_msg_received_count');
                } else {
                    Log::error('Erro ao enviar para o WhatsApp', [
                        'status' => $response->status(),
                        'message' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar para o WhatsApp', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Envia notificação via WhatsApp para o criador do formulário, informando que o limite de mensagens foi atingido.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return array
     */
    public function limitWhatsappMessage(Form $form): void
    {
        $formCreator = $form->user;
        $formCreator->notify(new NotificationUser(
            $formCreator,
            [
                'subject' => "Limite de mensagens WhatsApp atingido",
                'title' => "Olá, parece que voceu atingiu o limite de mensagens pelo WhatsApp. Veja mais detalhes no link:",
                'link' => "https://teste.com",
            ]
        ));
    }

    /**
     * Envia notificação via Webhook(se ativada) para a url salva no formulario,.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return void
     */
    public function notifyFormCreatorWenhook(Form $form, Respondent $respondent): void
    {
        if ($form->notification['webhook']['active'] && $form->notification['webhook']['url']) {

            try {
                $response = Http::post($form->notification['webhook']['url'], [
                    'form' => $form,
                    'respondent' => $respondent,
                    'message' => "Novo preenchimento no '{$form->title}' recebido. Confira no link: https://teste.com/api/forms/{$respondent->form_id}",
                ]);

                if (!$response->successful()) {
                    $this->notifyErrorWebhook($form, $response->status());

                    Log::error('Erro ao enviar Webhook', [
                        'status' => $response->status(),
                        'message' => $response->body(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Erro ao enviar para o Webhook', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Notifica o criador do formulário que houve um erro ao enviar o Webhook.
     *
     * @param Form $form
     * @param int $status
     * @return void
     */
    public function notifyErrorWebhook(Form $form, int $status): void
    {
        $formCreator = $form->user;
        $formCreator->notify(new NotificationUser(
            $formCreator,
            [
                'subject' => "Erro ao enviar Webhook",
                'title' => "Ocorreu um erro ao enviar o Webhook. Status: $status, Veja mais detalhes no link:",
                'link' => "https://teste.com",
            ]
        ));
    }
}
