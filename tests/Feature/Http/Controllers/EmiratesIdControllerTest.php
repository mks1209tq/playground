<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\EmiratesId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\EmiratesIdController
 */
final class EmiratesIdControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $emiratesIds = EmiratesId::factory()->count(3)->create();

        $response = $this->get(route('emirates-ids.index'));

        $response->assertOk();
        $response->assertViewIs('emiratesId.index');
        $response->assertViewHas('emiratesIds');
    }


    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('emirates-ids.create'));

        $response->assertOk();
        $response->assertViewIs('emiratesId.create');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EmiratesIdController::class,
            'store',
            \App\Http\Requests\EmiratesIdStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $document_type = $this->faker->word();
        $country_code = $this->faker->word();
        $card_number = $this->faker->word();
        $id_number = $this->faker->word();
        $date_of_birth = Carbon::parse($this->faker->date());
        $gender = $this->faker->word();
        $expiry_date = Carbon::parse($this->faker->date());
        $nationality = $this->faker->word();
        $surname = $this->faker->word();
        $given_names = $this->faker->word();

        $response = $this->post(route('emirates-ids.store'), [
            'document_type' => $document_type,
            'country_code' => $country_code,
            'card_number' => $card_number,
            'id_number' => $id_number,
            'date_of_birth' => $date_of_birth->toDateString(),
            'gender' => $gender,
            'expiry_date' => $expiry_date->toDateString(),
            'nationality' => $nationality,
            'surname' => $surname,
            'given_names' => $given_names,
        ]);

        $emiratesIds = EmiratesId::query()
            ->where('document_type', $document_type)
            ->where('country_code', $country_code)
            ->where('card_number', $card_number)
            ->where('id_number', $id_number)
            ->where('date_of_birth', $date_of_birth)
            ->where('gender', $gender)
            ->where('expiry_date', $expiry_date)
            ->where('nationality', $nationality)
            ->where('surname', $surname)
            ->where('given_names', $given_names)
            ->get();
        $this->assertCount(1, $emiratesIds);
        $emiratesId = $emiratesIds->first();

        $response->assertRedirect(route('emiratesIds.index'));
        $response->assertSessionHas('emiratesId.id', $emiratesId->id);
    }


    #[Test]
    public function show_displays_view(): void
    {
        $emiratesId = EmiratesId::factory()->create();

        $response = $this->get(route('emirates-ids.show', $emiratesId));

        $response->assertOk();
        $response->assertViewIs('emiratesId.show');
        $response->assertViewHas('emiratesId');
    }


    #[Test]
    public function edit_displays_view(): void
    {
        $emiratesId = EmiratesId::factory()->create();

        $response = $this->get(route('emirates-ids.edit', $emiratesId));

        $response->assertOk();
        $response->assertViewIs('emiratesId.edit');
        $response->assertViewHas('emiratesId');
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EmiratesIdController::class,
            'update',
            \App\Http\Requests\EmiratesIdUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $emiratesId = EmiratesId::factory()->create();
        $document_type = $this->faker->word();
        $country_code = $this->faker->word();
        $card_number = $this->faker->word();
        $id_number = $this->faker->word();
        $date_of_birth = Carbon::parse($this->faker->date());
        $gender = $this->faker->word();
        $expiry_date = Carbon::parse($this->faker->date());
        $nationality = $this->faker->word();
        $surname = $this->faker->word();
        $given_names = $this->faker->word();

        $response = $this->put(route('emirates-ids.update', $emiratesId), [
            'document_type' => $document_type,
            'country_code' => $country_code,
            'card_number' => $card_number,
            'id_number' => $id_number,
            'date_of_birth' => $date_of_birth->toDateString(),
            'gender' => $gender,
            'expiry_date' => $expiry_date->toDateString(),
            'nationality' => $nationality,
            'surname' => $surname,
            'given_names' => $given_names,
        ]);

        $emiratesId->refresh();

        $response->assertRedirect(route('emiratesIds.index'));
        $response->assertSessionHas('emiratesId.id', $emiratesId->id);

        $this->assertEquals($document_type, $emiratesId->document_type);
        $this->assertEquals($country_code, $emiratesId->country_code);
        $this->assertEquals($card_number, $emiratesId->card_number);
        $this->assertEquals($id_number, $emiratesId->id_number);
        $this->assertEquals($date_of_birth, $emiratesId->date_of_birth);
        $this->assertEquals($gender, $emiratesId->gender);
        $this->assertEquals($expiry_date, $emiratesId->expiry_date);
        $this->assertEquals($nationality, $emiratesId->nationality);
        $this->assertEquals($surname, $emiratesId->surname);
        $this->assertEquals($given_names, $emiratesId->given_names);
    }


    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $emiratesId = EmiratesId::factory()->create();

        $response = $this->delete(route('emirates-ids.destroy', $emiratesId));

        $response->assertRedirect(route('emiratesIds.index'));

        $this->assertSoftDeleted($emiratesId);
    }
}
