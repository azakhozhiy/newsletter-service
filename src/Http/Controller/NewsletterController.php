<?php

namespace WaHelp\Newsletter\Http\Controller;

use WaHelp\Core\Http\JsonResponse;
use WaHelp\Core\Http\Request;
use WaHelp\Newsletter\Service\NewsletterService;
use WaHelp\Newsletter\Service\UserNewsletterSender;

class NewsletterController
{
    public function send(
        Request $request,
        NewsletterService $newsletterService,
        UserNewsletterSender $userNewsletterService
    ): JsonResponse {
        // TODO: add idempotency
        $newsletter = $newsletterService->createByRequest($request);
        $sentCount = $userNewsletterService->send($newsletter);

        return new JsonResponse([
            'newsletter' => [
                'id' => $newsletter->getId(),
                'name' => $newsletter->getName(),
                'text' => $newsletter->getText(),
                'status' => $newsletter->getStatusEnum()->value,
            ],
            'sent_count' => $sentCount,
        ]);
    }
}