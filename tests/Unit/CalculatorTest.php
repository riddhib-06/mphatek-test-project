<?php

namespace Tests\Unit;

use App\Services\FdCalculator;
use App\Services\SipCalculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function test_cumulative_fd_is_compounded_quarterly(): void
    {
        $result = (new FdCalculator)->calculate(100000, 365, 7, 'cumulative');

        $this->assertEquals(107185.90, $result['maturity']);
        $this->assertEquals(7185.90, $result['interest']);
    }

    public function test_short_fd_gets_simple_interest(): void
    {
        $result = (new FdCalculator)->calculate(100000, 90, 6, 'cumulative');

        $this->assertEquals(1479.45, $result['interest']);
    }

    public function test_monthly_payout_fd(): void
    {
        $result = (new FdCalculator)->calculate(100000, 365, 7.2, 'monthly');

        $this->assertEquals(600, $result['payout_amount']);
        $this->assertEquals(7200, $result['interest']);
        $this->assertEquals(100000, $result['maturity']);
    }

    public function test_tenure_is_converted_to_days(): void
    {
        $this->assertEquals(730, FdCalculator::toDays(2, 'years'));
        $this->assertEquals(183, FdCalculator::toDays(6, 'months'));
        $this->assertEquals(45, FdCalculator::toDays(45, 'days'));
    }

    public function test_monthly_sip(): void
    {
        $result = (new SipCalculator)->calculate(5000, 'monthly', 12, 12);

        $this->assertEquals(12, $result['instalments']);
        $this->assertEquals(60000, $result['invested']);
        $this->assertEquals(64046.64, $result['future_value']);
    }

    public function test_quarterly_and_yearly_sip_instalments(): void
    {
        $sip = new SipCalculator;

        $this->assertEquals(20, $sip->calculate(10000, 'quarterly', 60, 10)['instalments']);
        $this->assertEquals(5, $sip->calculate(10000, 'yearly', 60, 10)['instalments']);
    }

    public function test_sip_with_zero_return(): void
    {
        $result = (new SipCalculator)->calculate(1000, 'monthly', 24, 0);

        $this->assertEquals(24000, $result['future_value']);
    }
}
