<?php

namespace App\Tests\Service;

use App\Service\UniqueFilenameGenerator;
use PHPUnit\Framework\TestCase;

class UniqueFilenameGeneratorTest extends TestCase
{

    public function testGenerateUniqueFilename()
    {
        $safeFilenameGenerator = new UniqueFilenameGenerator();

        $safeFilename = $safeFilenameGenerator->generateUniqueFilename('petit-chat-trop-mignongngnng', 'jpeg');

        $pattern = '/^image-' . preg_quote('petit-chat-trop-mignongngnng', '/') . '-[a-z0-9]{13}\.' . preg_quote('jpeg', '/') . '$/';

        $this->assertMatchesRegularExpression($pattern, $safeFilename);
    }


}