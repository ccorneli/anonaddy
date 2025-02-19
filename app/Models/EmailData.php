<?php

namespace App\Models;

use PhpMimeMailParser\Parser;

class EmailData
{
    public function __construct(Parser $parser, $size)
    {
        $this->sender = $parser->getAddresses('from')[0]['address'];
        $this->display_from = base64_encode($parser->getAddresses('from')[0]['display']);
        if (isset($parser->getAddresses('reply-to')[0])) {
            $this->reply_to_address = $parser->getAddresses('reply-to')[0]['address'];
        }

        if ($originalCc = $parser->getHeader('cc')) {
            $this->originalCc = $originalCc;
        }

        if ($originalTo = $parser->getHeader('to')) {
            $this->originalTo = $originalTo;
        }

        $this->subject = base64_encode($parser->getHeader('subject'));
        $this->text = base64_encode($parser->getMessageBody('text'));
        $this->html = base64_encode($parser->getMessageBody('html'));
        $this->attachments = [];
        $this->inlineAttachments = [];
        $this->size = $size;
        $this->messageId = base64_encode($parser->getHeader('Message-ID'));
        $this->listUnsubscribe = base64_encode($parser->getHeader('List-Unsubscribe'));
        $this->inReplyTo = base64_encode($parser->getHeader('In-Reply-To'));
        $this->references = base64_encode($parser->getHeader('References'));

        if ($parser->getParts()[1]['content-type'] === 'multipart/encrypted') {
            $this->encryptedParts = $parser->getAttachments();
        } else {
            foreach ($parser->getAttachments() as $attachment) {
                if ($attachment->getContentDisposition() === 'inline') {
                    $this->inlineAttachments[] = [
                        'stream' => base64_encode(stream_get_contents($attachment->getStream())),
                        'file_name' => base64_encode($attachment->getFileName()),
                        'mime' => base64_encode($attachment->getContentType()),
                        'contentDisposition' => base64_encode($attachment->getContentDisposition()),
                        'contentId' => base64_encode($attachment->getContentID()),
                    ];
                } else {
                    $this->attachments[] = [
                      'stream' => base64_encode(stream_get_contents($attachment->getStream())),
                      'file_name' => base64_encode($attachment->getFileName()),
                      'mime' => base64_encode($attachment->getContentType())
                  ];
                }
            }
        }
    }
}
