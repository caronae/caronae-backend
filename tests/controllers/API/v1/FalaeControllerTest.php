<?php

namespace Caronae\Http\Controllers\API\V1;

use Caronae\Mail\FalaeMessage;
use Caronae\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FalaeControllerTest extends TestCase
{
    /** @test */
    public function should_send_email_to_team()
    {
        Mail::fake();
        $user = factory(User::class)->create();
        $request = [
            'subject' => '[Reclamação] Motorista não compareceu',
            'message' => 'Combinei com o motorista e ele não compareceu no horário marcado.',
        ];

        $response = $this->jsonAs($user, 'POST', 'api/v1/falae/messages', $request);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'Message sent.']);

        Mail::assertQueued(FalaeMessage::class, function (FalaeMessage $mail) use ($user, $request) {
            return $mail->hasTo('caronae@fundoverde.ufrj.br')
                && $mail->hasReplyTo($user->email, $user->name)
                && $mail->subject == $request['subject']
                && $mail->userMessage == $request['message'];
        });
    }
}
