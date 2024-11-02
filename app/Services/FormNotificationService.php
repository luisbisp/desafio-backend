<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Form;
use App\Models\Respondent;
use App\Notifications\NotificationUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class FormNotificationService
{

    /**
     * Sends a notification to the form creator (if activated).
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
                $formCreator->email,
                [
                    'subject' => "Novo preenchimento no '{$form->title}'",
                    'title' => "Novo preenchimento no '{$form->title}' recebido. Confira no link:",
                    'link' => "https://teste.com/api/forms/{$respondent->form_id}",
                ]
            ));
        }
    }

    /**
     * Sends a notification via WhatsApp (if activated) to the form creator.
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
            return;
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
     * Sends a notification via WhatsApp to the form creator,
     * informing that the message limit has been reached
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return array
     */
    public function limitWhatsappMessage(Form $form): void
    {
        $formCreator = $form->user;
        $formCreator->notify(new NotificationUser(
            $formCreator->email,
            [
                'subject' => "Limite de mensagens WhatsApp atingido",
                'title' => "Olá, parece que voceu atingiu o limite de mensagens pelo WhatsApp. Veja mais detalhes no link:",
                'link' => "https://teste.com",
            ]
        ));
    }

    /**
     * Sends a notification via Webhook (if activated) to the URL saved in the form.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return void
     */
    public function notifyFormCreatorWebhook(Form $form, Respondent $respondent): void
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
     * Notifies the form creator that there was an error sending the Webhook.
     *
     * @param Form $form
     * @param int $status
     * @return void
     */
    public function notifyErrorWebhook(Form $form, int $status): void
    {
        $formCreator = $form->user;
        $formCreator->notify(new NotificationUser(
            $formCreator->email,
            [
                'subject' => "Erro ao enviar Webhook",
                'title' => "Ocorreu um erro ao enviar o Webhook. Status: $status, Veja mais detalhes no link:",
                'link' => "https://teste.com",
            ]
        ));
    }

    /**
     * Notifies the respondent via email that the form has been completed successfully.
     *
     * @param Form $form
     * @param Respondent $respondent
     * @return void
     */
    public function notifyFormRespondentEmail(Form $form, Respondent $respondent): void
    {
        if($form->notification['respondent_email']){

            $email =  Answer::getEmailAnswerByRespondent($respondent->public_id);

            $validate = Validator::make(['email' => $email], [
                'email' => 'required|email',
            ]);

            if ($validate->fails()) {
                return;
            }
    
            Notification::route('mail', $email)->notify(new NotificationUser(
                $email,
                [
                    'subject' => "Seu preenchimento do formulário '{$form->title}' foi completado",
                    'title' => "Novo preenchimento no '{$form->title}' recebido. Confira no link:",
                    'link' => "https://teste.com/api/forms/{$respondent->form_id}",
                ]
            ));
            
        }

    }
}
