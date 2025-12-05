<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\PhpCsFixer;

/**
 * Value object for generating copyright headers.
 *
 * Supports both open-source (MIT/GPL style) and proprietary headers.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class CopyrightHeader
{
    public function __construct(
        public string $holder,
        public bool $proprietary = false,
        public ?string $url = null,
    ) {}

    public function render(): string
    {
        if ($this->proprietary) {
            $header = "Copyright (C) {$this->holder} - All Rights Reserved\n\n";
            $header .= "Unauthorized copying, distribution, or use of this file in any manner\n";
            $header .= 'is strictly prohibited. This material is proprietary and confidential.';

            if ($this->url !== null) {
                $header .= '

For more details, see: '.$this->url;
            }

            return $header;
        }

        return "Copyright (C) {$this->holder}\n\nFor the full copyright and license information, please view the LICENSE\nfile that was distributed with this source code.";
    }
}
