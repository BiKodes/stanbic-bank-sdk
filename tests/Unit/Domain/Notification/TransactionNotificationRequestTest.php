<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Notification;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Notification\TransactionNotificationRequest;

final class TransactionNotificationRequestTest extends TestCase
{
    public function testCreateTransactionNotificationRequest(): void
    {
        $request = new TransactionNotificationRequest(
            ApiKey: 'XDETTTSSREFT',
            AlertType: 'Letter',
            AlertName: 'TRANSACTION',
            SendTo: 'example@stanbic.com',
            AccountNo: '100004790485',
            CustomerNo: '959043'
        );

        $this->assertSame('XDETTTSSREFT', $request->ApiKey);
        $this->assertSame('Letter', $request->AlertType);
        $this->assertSame('TRANSACTION', $request->AlertName);
        $this->assertSame('example@stanbic.com', $request->SendTo);
        $this->assertSame('100004790485', $request->AccountNo);
        $this->assertSame('959043', $request->CustomerNo);
    }

    public function testFromArrayWithLetterStructure(): void
    {
        $data = [
            'Letter' => [
                'AlertType' => 'Letter',
                'AlertName' => 'TRANSACTION',
                'SendTo' => 'example@stanbic.com',
                'AccountNo' => '100004790485',
                'CustomerNo' => '959043',
                'Subject' => 'e-Alert on Account',
                'CustomerName' => 'XXX CO.LTD.',
                'Date' => '10/03/2020 at 09:11AM',
                'ValueDate' => '10/03/2020',
                'ActionType' => 'DEBIT',
                'TxnDescr' => 'Account Transfer',
                'TxnAmount' => 'KES 1,234.00',
                'OurTxnReference' => 'FT20070YCTF8999\\BNK',
                'ThirdPartyRef' => 'MXProoWECCC',
                'TransactionOriginationBranch' => 'Head Office - UAT',
                'Narrative' => 'Account Transfer\\5677888888\\nTEST',
                'CurrentBalance' => 'KES 344,018,454.07',
                'AvailableBalance' => 'KES 344,018,454.07',
                'ClearedBalance' => 'KES 344,018,454.07',
                'AccountOfficer' => '2609',
                'ApiKey' => 'XDETTTSSREFT',
                'ClientFormat' => 'XML/JSON',
                'MSISDN' => '254712345678',
                'PAYER.REF' => '40000',
                'CallbackUrl' => 'https://example.com',
            ],
        ];

        $request = TransactionNotificationRequest::fromArray($data);

        $this->assertSame('XDETTTSSREFT', $request->ApiKey);
        $this->assertSame('Letter', $request->AlertType);
        $this->assertSame('TRANSACTION', $request->AlertName);
        $this->assertSame('example@stanbic.com', $request->SendTo);
        $this->assertSame('100004790485', $request->AccountNo);
        $this->assertSame('959043', $request->CustomerNo);
        $this->assertSame('e-Alert on Account', $request->Subject);
        $this->assertSame('XXX CO.LTD.', $request->CustomerName);
        $this->assertSame('10/03/2020 at 09:11AM', $request->Date);
        $this->assertSame('10/03/2020', $request->ValueDate);
        $this->assertSame('DEBIT', $request->ActionType);
        $this->assertSame('Account Transfer', $request->TxnDescr);
        $this->assertSame('KES 1,234.00', $request->TxnAmount);
        $this->assertSame('FT20070YCTF8999\\BNK', $request->OurTxnReference);
        $this->assertSame('MXProoWECCC', $request->ThirdPartyRef);
        $this->assertSame('Head Office - UAT', $request->TransactionOriginationBranch);
        $this->assertSame('Account Transfer\\5677888888\\nTEST', $request->Narrative);
        $this->assertSame('KES 344,018,454.07', $request->CurrentBalance);
        $this->assertSame('KES 344,018,454.07', $request->AvailableBalance);
        $this->assertSame('KES 344,018,454.07', $request->ClearedBalance);
        $this->assertSame('2609', $request->AccountOfficer);
        $this->assertSame('XML/JSON', $request->ClientFormat);
        $this->assertSame('254712345678', $request->MSISDN);
        $this->assertSame('40000', $request->PayerRef);
        $this->assertSame('https://example.com', $request->CallbackUrl);
    }

    public function testFromArrayWithoutLetterWrapper(): void
    {
        $data = [
            'ApiKey' => 'XDETTTSSREFT',
            'AlertType' => 'Letter',
            'AlertName' => 'TRANSACTION',
            'SendTo' => 'example@stanbic.com',
        ];

        $request = TransactionNotificationRequest::fromArray($data);

        $this->assertSame('XDETTTSSREFT', $request->ApiKey);
        $this->assertSame('Letter', $request->AlertType);
        $this->assertSame('TRANSACTION', $request->AlertName);
        $this->assertSame('example@stanbic.com', $request->SendTo);
    }

    public function testFromArrayWithCamelCase(): void
    {
        $data = [
            'apiKey' => 'XDETTTSSREFT',
            'alertType' => 'Letter',
            'txnAmount' => 'KES 5,000.00',
        ];

        $request = TransactionNotificationRequest::fromArray($data);

        $this->assertSame('XDETTTSSREFT', $request->ApiKey);
        $this->assertSame('Letter', $request->AlertType);
        $this->assertSame('KES 5,000.00', $request->TxnAmount);
    }

    public function testToArray(): void
    {
        $request = new TransactionNotificationRequest(
            ApiKey: 'XDETTTSSREFT',
            AlertType: 'Letter',
            AlertName: 'TRANSACTION',
            SendTo: 'example@stanbic.com',
            AccountNo: '100004790485'
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('Letter', $array);
        $this->assertIsArray($array['Letter']);
        $this->assertSame('XDETTTSSREFT', $array['Letter']['ApiKey']);
        $this->assertSame('Letter', $array['Letter']['AlertType']);
        $this->assertSame('TRANSACTION', $array['Letter']['AlertName']);
        $this->assertSame('example@stanbic.com', $array['Letter']['SendTo']);
        $this->assertSame('100004790485', $array['Letter']['AccountNo']);
    }

    public function testToArrayOmitsNullFields(): void
    {
        $request = new TransactionNotificationRequest(
            ApiKey: 'XDETTTSSREFT'
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('Letter', $array);
        $this->assertIsArray($array['Letter']);
        $this->assertArrayHasKey('ApiKey', $array['Letter']);
        $this->assertArrayNotHasKey('AlertType', $array['Letter']);
        $this->assertArrayNotHasKey('AlertName', $array['Letter']);
        $this->assertArrayNotHasKey('SendTo', $array['Letter']);
    }

    public function testToArrayWithAllFields(): void
    {
        $request = new TransactionNotificationRequest(
            ApiKey: 'XDETTTSSREFT',
            AlertType: 'Letter',
            AlertName: 'TRANSACTION',
            SendTo: 'example@stanbic.com',
            AccountNo: '100004790485',
            CustomerNo: '959043',
            Subject: 'e-Alert on Account',
            CustomerName: 'XXX CO.LTD.',
            Date: '10/03/2020 at 09:11AM',
            ValueDate: '10/03/2020',
            ActionType: 'DEBIT',
            TxnDescr: 'Account Transfer',
            TxnAmount: 'KES 1,234.00',
            OurTxnReference: 'FT20070YCTF8999\\BNK',
            ThirdPartyRef: 'MXProoWECCC',
            TransactionOriginationBranch: 'Head Office - UAT',
            Narrative: 'Account Transfer\\5677888888\\nTEST',
            CurrentBalance: 'KES 344,018,454.07',
            AvailableBalance: 'KES 344,018,454.07',
            ClearedBalance: 'KES 344,018,454.07',
            AccountOfficer: '2609',
            ClientFormat: 'XML/JSON',
            MSISDN: '254712345678',
            PayerRef: '40000',
            CallbackUrl: 'https://example.com'
        );

        $array = $request->toArray();

        $this->assertArrayHasKey('Letter', $array);
        $this->assertIsArray($array['Letter']);
        $letter = $array['Letter'];

        $this->assertSame('XDETTTSSREFT', $letter['ApiKey']);
        $this->assertSame('Letter', $letter['AlertType']);
        $this->assertSame('TRANSACTION', $letter['AlertName']);
        $this->assertSame('example@stanbic.com', $letter['SendTo']);
        $this->assertSame('100004790485', $letter['AccountNo']);
        $this->assertSame('959043', $letter['CustomerNo']);
        $this->assertSame('e-Alert on Account', $letter['Subject']);
        $this->assertSame('XXX CO.LTD.', $letter['CustomerName']);
        $this->assertSame('10/03/2020 at 09:11AM', $letter['Date']);
        $this->assertSame('10/03/2020', $letter['ValueDate']);
        $this->assertSame('DEBIT', $letter['ActionType']);
        $this->assertSame('Account Transfer', $letter['TxnDescr']);
        $this->assertSame('KES 1,234.00', $letter['TxnAmount']);
        $this->assertSame('FT20070YCTF8999\\BNK', $letter['OurTxnReference']);
        $this->assertSame('MXProoWECCC', $letter['ThirdPartyRef']);
        $this->assertSame('Head Office - UAT', $letter['TransactionOriginationBranch']);
        $this->assertSame('Account Transfer\\5677888888\\nTEST', $letter['Narrative']);
        $this->assertSame('KES 344,018,454.07', $letter['CurrentBalance']);
        $this->assertSame('KES 344,018,454.07', $letter['AvailableBalance']);
        $this->assertSame('KES 344,018,454.07', $letter['ClearedBalance']);
        $this->assertSame('2609', $letter['AccountOfficer']);
        $this->assertSame('XML/JSON', $letter['ClientFormat']);
        $this->assertSame('254712345678', $letter['MSISDN']);
        $this->assertSame('40000', $letter['PAYER.REF']);
        $this->assertSame('https://example.com', $letter['CallbackUrl']);
    }
}
