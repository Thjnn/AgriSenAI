<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', ['products' => $this->generateProducts(), 'categories' => $this->getCategories()]);
    }

    private function getCategories(): array
    {
        return ['trai-cay', 'dac-san'];
    }

    private function generateProducts(): array
    {
        $products = [];
        $data = [
            'trai-cay' => [
                ['Xoài Cao Lãnh loại 1', 'Vườn ông Sáu Tâm', 32000, 'kg', 94, true, 'Chín tới', 'Không phát hiện', 'Chất lượng tốt, giá thị trường đang cao và thời tiết thuận lợi vận chuyển — nên bán trong 3–5 ngày tới.'],
                ['Quýt hồng Lai Vung', 'HTX Lai Vung', 45000, 'kg', 88, true, 'Vừa chín', 'Không phát hiện', 'Còn có thể chờ thêm 4–6 ngày để trái chín đều hơn, giá dự kiến tăng nhẹ theo mùa vụ.'],
                ['Nhãn tiêu da bò Châu Thành', 'Vườn cô Bảy Hoa', 28000, 'kg', 91, true, 'Đạt chuẩn', 'Không phát hiện', 'Chất lượng ổn định, nhu cầu thị trường đang cao — nên đăng bán ngay.'],
                ['Xoài Cao Lãnh loại 2', 'Vườn chị Kim Anh', 22000, 'kg', 68, false, 'Hơi chín quá', 'Phát hiện dấu hiệu dập nhẹ ở 2 điểm', 'Nên giảm giá 10–15% và ưu tiên bán trong 1–2 ngày tới.'],
                ['Quýt hồng loại xuất khẩu', 'HTX Lai Vung', 58000, 'kg', 97, true, 'Chín tới', 'Không phát hiện', 'Chất lượng xuất sắc, phù hợp gắn nhãn cao cấp — có thể giữ giá cao trong 5–7 ngày tới.'],
                ['Bưởi da xanh', 'Vườn chú Ba', 40000, 'kg', 93, true, 'Đạt chuẩn', 'Không phát hiện', 'Mùa vụ thuận lợi, nên xuất bán sỉ cho các chợ đầu mối.'],
                ['Chôm chôm Thái', 'Vườn anh Tư', 25000, 'kg', 86, true, 'Chín đều', 'Không phát hiện', 'Sản lượng nhiều, nên bán kết hợp online và chợ truyền thống.'],
                ['Sầu riêng Ri6', 'Vườn cô Mười', 95000, 'kg', 96, true, 'Chín đạt chuẩn', 'Không phát hiện', 'Giá đang ở đỉnh điểm, nên thu hoạch và bán ngay trong tuần.'],
                ['Măng cụt', 'Vườn ông Bảy', 55000, 'kg', 90, true, 'Chín tới', 'Không phát hiện', 'Chất lượng ổn định, nên đóng gói đẹp để bán làm quà.'],
                ['Thanh long ruột đỏ', 'Hợp tác xã Bình Thuận', 35000, 'kg', 89, true, 'Đạt chuẩn', 'Không phát hiện', 'Nhu cầu xuất khẩu tăng, nên đẩy mạnh sản lượng.'],
            ],
            'dac-san' => [
                ['Xoài sấy dẻo Cao Lãnh', 'Cơ sở Út Hồng', 85000, 'kg', 90, true, 'Đạt chuẩn', 'Không phát hiện', 'Sấy dẻo giữ vị chua ngọt tự nhiên, phù hợp bán làm quà biếu.'],
                ['Mứt xoài Đồng Tháp', 'Cơ sở Mười Bé', 60000, 'kg', 88, true, 'Thơm ngon', 'Không phát hiện', 'Mứt xoài truyền thống, đóng gói đẹp để làm quà Tết.'],
                ['Quýt hồng sấy dẻo Lai Vung', 'HTX Lai Vung', 95000, 'kg', 93, true, 'Đạt chuẩn', 'Không phát hiện', 'Giữ nguyên hương vị đặc trưng, nhu cầu quà biếu đang cao.'],
                ['Rượu xoài Cao Lãnh', 'Cơ sở Nếp Quê', 70000, 'chai', 91, true, 'Đạt chuẩn', 'Không phát hiện', 'Lên men từ xoài chín cây, hương vị đặc trưng, phù hợp làm quà biếu.'],
                ['Ô mai xoài chua ngọt', 'Cơ sở Quê Hương', 45000, 'kg', 87, true, 'Đóng gói đẹp', 'Không phát hiện', 'Vị chua ngọt hài hòa, phù hợp bán online toàn quốc.'],
                ['Nước cốt xoài nguyên chất', 'Cơ sở Tràm U Minh', 55000, 'chai', 94, true, 'Đạt chuẩn', 'Không phát hiện', 'Không chất bảo quản, nên bảo quản lạnh khi vận chuyển xa.'],
                ['Mứt bưởi da xanh', 'Vườn chú Ba', 65000, 'kg', 89, true, 'Đạt chuẩn', 'Không phát hiện', 'Vỏ bưởi sên đường thủ công, giòn dai, phù hợp làm quà.'],
                ['Chôm chôm sấy dẻo', 'Vườn anh Tư', 75000, 'kg', 92, true, 'Đạt chuẩn', 'Không phát hiện', 'Sấy dẻo giữ vị ngọt tự nhiên, sản lượng ổn định.'],
                ['Kẹo xoài Cao Lãnh', 'Lò bánh Tư Sửa', 40000, 'gói', 90, true, 'Mới sản xuất', 'Không phát hiện', 'Kẹo dẻo vị xoài truyền thống, đóng gói hút chân không.'],
                ['Trà xoài sấy lạnh', 'Lò bánh Mỹ Lan', 50000, 'kg', 90, true, 'Mới sản xuất', 'Không phát hiện', 'Sấy lạnh giữ hương vị, pha trà thơm tự nhiên.'],
            ],
        ];

        $ripenessOptions = ['Tươi ngon', 'Đạt chuẩn', 'Chất lượng cao', 'Còn tươi'];
        $defectOptions = ['Không phát hiện', 'Không phát hiện', 'Không phát hiện', 'Phát hiện hao mòn nhẹ'];
        $recoOptions = [
            'Chất lượng ổn định, nhu cầu thị trường cao — nên đăng bán ngay.',
            'Sản phẩm đạt chuẩn, giá cạnh tranh so với đối thủ.',
            'Phù hợp cho nhiều loại khách hàng, nên bán kèm gói quà tặng.',
            'Độ tươi ngon đảm bảo, phù hợp cho xuất khẩu.',
            'Cần bảo quản lạnh khi vận chuyển xa, nên bán trong khu vực.',
        ];

        $id = 0;
        foreach ($data as $cat => $items) {
            foreach ($items as $item) {
                $id++;
                $price = $item[2];
                $products[] = (object) [
                    'id' => $id,
                    'cat' => $cat,
                    'name' => $item[0],
                    'farmer' => $item[1],
                    'region' => $this->getRegion($cat),
                    'ocop' => $this->getOcop($id),
                    'price' => number_format($price, 0, ',', '.'),
                    'unit' => $item[3],
                    'ai' => (object) [
                        'good' => true,
                        'ripeness' => $item[6],
                        'score' => $item[4],
                        'defect' => $item[7],
                        'reco' => $item[8],
                    ],
                ];
            }
        }

        return $products;
    }

    private function getRegion(string $cat): string
    {
        return match ($cat) {
            'trai-cay' => 'Đồng Tháp',
            'dac-san' => 'Đồng Tháp',
            default => 'Việt Nam',
        };
    }

    private function getOcop(int $id): string
    {
        $labels = ['OCOP 3★', 'OCOP 4★', 'OCOP 5★', 'Đặc sản', 'Hữu cơ', 'VietGAP', 'Hàng Việt Nam Chất Lượng Cao'];
        return $labels[$id % count($labels)];
    }
}
