<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDigitalCardRequest;
use App\Models\DigitalCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DigitalCardController extends Controller
{
    public function edit(): View
    {
        return view('admin.digital-card.edit', ['card' => DigitalCard::current()]);
    }

    public function update(UpdateDigitalCardRequest $request): RedirectResponse
    {
        DigitalCard::current()->update($request->validated());

        return redirect()->route('admin.digital-card.edit')->with('status', 'Digital card updated.');
    }
}
