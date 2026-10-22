<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\LotWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Du lieu demo: 3 tai khoan + 3 lo da cong bo + 1 lo dang lam.
 *
 * Di qua LotWorkflow nhu nguoi dung that -> anh duoc chuan hoa + bam SHA-256,
 * nhat ky noi chuoi bam that, trang QR verify duoc.
 *
 * Nhan demo deu la NHAP THU CONG (decision = manual): khong bia "goi y AI" de
 * so lieu ti le chap nhan AI tren trang Thong ke la so that.
 */
class DemoLotSeeder extends Seeder
{
    public const PASSWORD = 'agrisen123';

    public function run(LotWorkflow $wf): void
    {
        $staff = Role::where('name', Role::NHAN_VIEN)->value('id');
        $admin = Role::where('name', Role::ADMIN)->value('id');

        User::create(['role_id' => $admin, 'name' => 'Quản trị AgriSen', 'phone' => '0900000000', 'org_name' => 'AgriSenAi', 'password' => self::PASSWORD]);
        $myXuong = User::create(['role_id' => $staff, 'name' => 'Nguyễn Văn Tâm', 'phone' => '0911111111', 'org_name' => 'Vựa xoài Mỹ Xương', 'password' => self::PASSWORD]);
        $caoLanh = User::create(['role_id' => $staff, 'name' => 'Trần Thị Hoa', 'phone' => '0922222222', 'org_name' => 'HTX Xoài Cao Lãnh', 'password' => self::PASSWORD]);

        $lots = [
            [$myXuong, ['supplier' => 'Vườn ông Sáu Tâm, Mỹ Xương', 'variety' => 'Cát Hòa Lộc', 'purpose' => 'an_chin', 'weight_kg' => 420], [['xoai-mau2', 'toan_qua'], ['xoai-mau8', 'be_mat']], [
                'color' => ['value' => 'vang', 'note' => 'Vàng đều, vỏ căng bóng'],
                'ripeness' => ['value' => 'chin_toi', 'note' => 'Chín tới, đúng mục đích ăn chín'],
                'surface' => ['value' => 'sach', 'note' => 'Không thấy khuyết tật'],
                'shape' => ['value' => 'binh_thuong'],
                'uniformity' => ['value' => 'dong_deu', 'note' => 'Các quả cùng cỡ, cùng màu'],
            ]],
            [$caoLanh, ['supplier' => 'Tổ hợp tác Tịnh Thới', 'variety' => 'Keo', 'purpose' => 'an_xanh', 'weight_kg' => 1250], [['xoai-mau3', 'toan_qua'], ['xoai-mau6', 'be_mat']], [
                'color' => ['value' => 'xanh', 'note' => 'Xanh đậm, phù hợp ăn xanh'],
                'ripeness' => ['value' => 'xanh_gia', 'note' => 'Xanh già, thịt chắc — đúng yêu cầu ăn xanh'],
                'surface' => ['value' => 'khuyet_tat_nhe', 'defect_types' => ['vet_nhua'], 'note' => 'Vài vết nhựa nhỏ gần cuống, chưa ăn vào thịt'],
                'shape' => ['value' => 'binh_thuong'],
                'uniformity' => ['value' => 'dong_deu'],
            ]],
            [$myXuong, ['supplier' => 'Vườn cô Bảy Hoa, Tháp Mười', 'variety' => 'Đài Loan', 'purpose' => 'an_chin', 'weight_kg' => 300], [['xoai-mau10', 'toan_qua']], [
                'color' => ['value' => 'vang_cam', 'note' => 'Vàng cam, vài quả còn ửng xanh ở vai'],
                'ripeness' => ['value' => 'chin_ky', 'note' => 'Chín kỹ — nên bán trong 2–3 ngày'],
                'surface' => ['value' => 'khuyet_tat_nhe', 'defect_types' => ['dom_den'], 'note' => 'Đốm đen nhỏ rải rác ~2% bề mặt, còn nông'],
                'shape' => ['value' => 'meo_nhe', 'note' => 'Một số quả hơi cong'],
                'uniformity' => ['value' => 'khong_deu', 'note' => 'Cỡ quả chênh nhau'],
            ]],
        ];

        // Chot moc truoc vong lap: setTestNow ben duoi lam now() troi theo.
        $today = now()->startOfDay();

        foreach ($lots as $i => [$user, $data, $photos, $labels]) {
            // Lui thoi gian de trang Thong ke co so "thoi gian lap ho so" hop ly.
            $start = $today->copy()->subDays(3 - $i)->setTime(7 + $i, 10);
            Carbon::setTestNow($start);
            $lot = $wf->create($data + ['harvest_date' => $start->copy()->subDay()->toDateString()], $user);

            foreach ($photos as $k => [$file, $view]) {
                Carbon::setTestNow($start->copy()->addMinutes(2 + $k));
                $wf->addPhoto($lot, public_path("images/products/{$file}.jpg"), $view, $user);
            }

            Carbon::setTestNow($start->copy()->addMinutes(9 + $i * 3));
            $wf->confirm($lot, array_map(fn ($v) => ['decision' => 'manual'] + $v, $labels), $user);

            Carbon::setTestNow($start->copy()->addMinutes(11 + $i * 3));
            $wf->publish($lot->fresh(), $user);
        }

        // Mot lo dang lam do: moi co anh, chua xac nhan.
        Carbon::setTestNow($today->copy()->setTime(8, 0));
        $draft = $wf->create(['supplier' => 'Vườn chú Ba, Cao Lãnh', 'variety' => 'Cát Chu', 'purpose' => 'an_chin', 'weight_kg' => 180, 'harvest_date' => now()->toDateString()], $caoLanh);
        $wf->addPhoto($draft, public_path('images/products/xoai-mau11.jpg'), 'toan_qua', $caoLanh);

        Carbon::setTestNow();
    }
}
