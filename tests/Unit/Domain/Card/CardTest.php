<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Card;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Card\Card;

final class CardTest extends TestCase
{
    public function testCreateCard(): void
    {
        $card = new Card(
            pan: '4069051007375838',
            status: '200',
            cardholdername: 'MR',
            paymentScheme: 'VISA'
        );

        $this->assertSame('4069051007375838', $card->pan);
        $this->assertSame('200', $card->status);
        $this->assertSame('MR', $card->cardholdername);
        $this->assertSame('VISA', $card->paymentScheme);
    }

    public function testFromArray(): void
    {
        $data = [
            'pan' => '4069051007375838',
            'status' => '200',
            'cardholdername' => 'MR',
            'cardholderName' => 'JOHN DOE',
            'paymentScheme' => 'PULL_VISA',
            'expiryYear' => '2030',
            'expiryMonth' => '12',
        ];

        $card = Card::fromArray($data);

        $this->assertSame('4069051007375838', $card->pan);
        $this->assertSame('200', $card->status);
        $this->assertSame('MR', $card->cardholdername);
        $this->assertSame('JOHN DOE', $card->cardholderName);
        $this->assertSame('PULL_VISA', $card->paymentScheme);
        $this->assertSame('2030', $card->expiryYear);
        $this->assertSame('12', $card->expiryMonth);
    }

    public function testToArray(): void
    {
        $card = new Card(
            pan: '0100011231394',
            status: '200',
            cardholdername: 'MR',
            paymentScheme: 'SURNAME',
            expiryYear: '2025',
            expiryMonth: '06'
        );

        $array = $card->toArray();

        $this->assertSame('0100011231394', $array['pan']);
        $this->assertSame('200', $array['status']);
        $this->assertSame('MR', $array['cardholdername']);
        $this->assertSame('SURNAME', $array['paymentScheme']);
        $this->assertSame('2025', $array['expiryYear']);
        $this->assertSame('06', $array['expiryMonth']);
    }

    public function testToArrayWithPartialData(): void
    {
        $card = new Card(
            pan: '4069051007375838',
            status: null,
            cardholdername: 'MR'
        );

        $array = $card->toArray();

        $this->assertArrayHasKey('pan', $array);
        $this->assertArrayHasKey('cardholdername', $array);
        $this->assertArrayNotHasKey('status', $array);
        $this->assertArrayNotHasKey('paymentScheme', $array);
    }

    public function testToArrayEmpty(): void
    {
        $card = new Card();

        $this->assertSame([], $card->toArray());
    }

    public function testFromArrayWithAllProperties(): void
    {
        $data = [
            'pan' => '1234567890123456',
            'status' => 'ACTIVE',
            'cardholdername' => 'MR SMITH',
            'cardholderName' => 'JOHN SMITH',
            'paymentScheme' => 'MASTERCARD',
            'expiryYear' => '2028',
            'expiryMonth' => '03',
        ];

        $card = Card::fromArray($data);

        $array = $card->toArray();

        $this->assertSame($data['pan'], $array['pan']);
        $this->assertSame($data['status'], $array['status']);
        $this->assertSame($data['cardholdername'], $array['cardholdername']);
        $this->assertSame($data['cardholderName'], $array['cardholderName']);
        $this->assertSame($data['paymentScheme'], $array['paymentScheme']);
        $this->assertSame($data['expiryYear'], $array['expiryYear']);
        $this->assertSame($data['expiryMonth'], $array['expiryMonth']);
    }
}
