<?php

namespace App\Helpers;

use nadar\quill\Lexer;

class NotificationContentRenderHelper
{
    public static function renderContent($content)
    {
        $lexer = new Lexer($content);
        return $lexer->render();
    }
}
