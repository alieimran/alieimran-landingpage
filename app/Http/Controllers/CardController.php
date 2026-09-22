<?php

namespace App\Http\Controllers;

use App\Models\DigitalCard;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CardController extends Controller
{
    public function __invoke(): View|Response
    {
        $card = DigitalCard::current();

        abort_unless($card->enabled, 404);

        $qrCode = (new Builder(
            writer: new PngWriter,
            data: route('card'),
            size: 220,
            margin: 8,
        ))->build();

        return view('card', [
            'card' => $card,
            'qrDataUri' => $qrCode->getDataUri(),
        ]);
    }
}
