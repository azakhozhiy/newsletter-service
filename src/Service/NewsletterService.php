<?php

namespace WaHelp\Newsletter\Service;

use WaHelp\Core\Exception\Http\RequestValidationException;
use WaHelp\Core\Http\Request;
use WaHelp\Newsletter\Entity\Newsletter;
use WaHelp\Newsletter\Enum\NewsletterStatusEnum;
use WaHelp\Newsletter\Repository\NewsletterRepository;

class NewsletterService
{
    public function __construct(protected NewsletterRepository $newsletterRepository)
    {
    }

    public function createByRequest(Request $request): Newsletter
    {
        $name = $request->input('name');
        $text = $request->input('text');

        if ($name && $text) {
            return $this->newsletterRepository->create([
                'name' => $name,
                'text' => $text,
                'status' => NewsletterStatusEnum::IN_PROGRESS->value
            ]);
        }

        throw new RequestValidationException($request, "Name and text can't be empty");
    }
}