<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    // Kế thừa Tests\TestCase (có boot app) chứ không phải PHPUnit\Framework\TestCase:
    // User dùng #[Fillable] — attribute của Laravel 13 cần container để đọc.
    // Vẫn không đụng database vì chỉ dùng setRelation().

    public function test_nong_dan_thi_isFarmer_tra_ve_true(): void
    {
        $user = new User();
        $user->setRelation('role', new Role(['name' => 'nong_dan']));

        $this->assertTrue($user->isFarmer());
    }

    public function test_nguoi_mua_thi_isFarmer_tra_ve_false(): void
    {
        $user = new User();
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->assertFalse($user->isFarmer());
    }

    public function test_chua_co_role_thi_coi_nhu_nguoi_mua(): void
    {
        $user = new User();
        $user->setRelation('role', null);

        $this->assertFalse($user->isFarmer());
    }

    public function test_accountHref_khac_nhau_theo_vai_tro(): void
    {
        $farmer = new User();
        $farmer->setRelation('role', new Role(['name' => 'nong_dan']));

        $buyer = new User();
        $buyer->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->assertNotSame($farmer->accountHref(), $buyer->accountHref());
    }
}
