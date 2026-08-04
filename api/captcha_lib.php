<?php
// =============================================================================
// 檔案：captcha_lib.php
// 用途：圖形驗證碼的「共用函式庫」
// =============================================================================
//
// 【這個檔案在整套機制中的角色】
//
//   瀏覽器（前端頁面，例如 image.php）
//        │
//        ├── ajax GET  ──→ captcha_image.php  ──┐
//        │                （發圖服務）            │
//        │                                       ├──→ captcha_lib.php（本檔）
//        └── ajax POST ──→ captcha_verify.php ──┘     ↑ 共用的邏輯都放這裡
//                         （驗證服務）
//
//   本檔案「只定義函式，不會輸出任何東西」，所以：
//     - 它不能被瀏覽器直接開啟來用（開了也只會看到一片空白）
//     - 兩支服務用 require_once 把它載入，就能共用同一組產碼、繪圖、比對邏輯
//     - 以後要改圖片大小、干擾線數量、比對規則，全部只要改這一個檔案
//
// 【為什麼要拆成「函式庫 + 兩支服務」？】
//   原本 image.php 一個檔案同時做三件事：產生驗證碼、畫圖、顯示網頁。
//   拆開之後每個檔案只負責一件事，好處是：
//     1. 任何頁面都能呼叫這兩支服務，不必把驗證碼的程式碼複製一份
//     2. 修 bug 只要改一個地方
//     3. 前端可以用 ajax 局部更新驗證碼，不必整頁重新整理
//
// 【驗證碼的運作原理（重要觀念）】
//   驗證碼的「正確答案」全程存在伺服器的 Session 裡，永遠不會傳給瀏覽器。
//   傳給瀏覽器的只有「畫成圖片的樣子」，人眼看得懂、程式不容易讀。
//   使用者輸入答案後送回伺服器，由伺服器拿 Session 裡的答案來比對。
//   所以答案不可能被前端的 JavaScript 偷看，這就是驗證碼安全的關鍵。
// =============================================================================


// -----------------------------------------------------------------------------
// 設定區：這兩個常數決定整套機制的行為，要調整先從這裡看
// -----------------------------------------------------------------------------

// 驗證碼的正確答案要存在 Session 的哪一個「鍵值（key）」底下。
// Session 可以想成「伺服器幫每位使用者準備的一個置物櫃」，
// 這個常數就是置物櫃裡放驗證碼的那一格的名字。
// 發圖服務用這個名字「放進去」，驗證服務用同一個名字「拿出來比對」，
// 所以兩邊一定要用同一個名字，寫成常數就不會有人打錯字。
const CAPTCHA_SESSION_KEY = 'captcha_code';

// 比對答案時要不要忽略英文大小寫。
//   true  = 圖片顯示 aBc，使用者打 ABC 也算對（對使用者比較友善，一般網站多半這樣做）
//   false = 必須一模一樣才算對（比較嚴格，但使用者容易因為大小寫而失敗）
const CAPTCHA_CASE_INSENSITIVE = true;


// ---- 圖片外觀設定 ------------------------------------------------------------
// 下面這些數字決定驗證碼「長什麼樣子」，想調整視覺效果改這裡就好，
// 不必去動 createCaptchaImage() 裡面的繪圖程式碼。

// 圖片的「最小」尺寸。
// 特別注意是「最小」不是「固定」：因為字元會旋轉，旋轉後佔的空間會變大，
// 所以真正的圖片尺寸是程式量測完文字之後才決定的，只是不會小於這兩個值。
// 這樣可以確保驗證碼只有 4 個字時，圖也不會縮得太小、版面跳來跳去。
const CAPTCHA_MIN_WIDTH  = 180;
const CAPTCHA_MIN_HEIGHT = 40;

// 文字四周要留多少空白（留白，padding）。
// 留白不夠的話，旋轉後的字角會貼到邊框甚至被切掉。
const CAPTCHA_PADDING_X = 16;
const CAPTCHA_PADDING_Y = 10;

// TrueType 字型的字級大小。
const CAPTCHA_FONT_SIZE = 24;

// 每個字元最多可以左右旋轉幾度。
// 15 代表每個字會在 -15 度（向右倒）到 +15 度（向左倒）之間隨機選一個角度。
// 角度太大人眼也會看不懂，一般抓 10~20 度之間。
const CAPTCHA_MAX_ANGLE = 15;

// 字元與字元之間的間隔（像素）。
// 因為每個字是分開畫的，沒有這個間隔字會擠在一起。
// 想再增加辨識難度可以改成負數，讓字元稍微重疊。
const CAPTCHA_CHAR_GAP = 3;

// 干擾線兩端要落在左右邊緣多寬的範圍內（佔整張圖寬度的比例）。
// 0.15 代表左端點落在最左邊 15% 的範圍、右端點落在最右邊 15% 的範圍，
// 這樣每條線一定會橫跨整個文字區域，不會只在角落畫一小段。
const CAPTCHA_EDGE_BAND_RATIO = 0.15;

// 干擾線的數量範圍（每次隨機取一個數量）。
const CAPTCHA_LINE_MIN = 4;
const CAPTCHA_LINE_MAX = 6;


// -----------------------------------------------------------------------------
// 函式：captchaStartSession()
// 功能：確保 Session 已經啟動
// 回傳：無
// -----------------------------------------------------------------------------
// PHP 要先呼叫 session_start()，$_SESSION 這個超級全域變數才能用。
// 但同一次請求裡「重複呼叫」session_start() 會跳出警告訊息（Notice），
// 而警告訊息一旦被印出來，就會混進我們的 JSON 或 PNG 內容裡把它弄壞。
//
// 所以這裡先用 session_status() 檢查目前狀態：
//   PHP_SESSION_ACTIVE = 已經啟動了 → 什麼都不用做
//   其他狀態           = 還沒啟動   → 呼叫 session_start()
//
// 有了這個保護，底下每個需要 Session 的函式都可以放心呼叫它，
// 不必去煩惱「到底前面有沒有人開過 Session」。
function captchaStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}


// -----------------------------------------------------------------------------
// 函式：generateCaptchaCode(?int $length = null)
// 功能：隨機產生一組驗證碼字串
// 參數：$length = 要幾個字元；傳 null（預設）就隨機決定 4~8 個字
// 回傳：產生出來的驗證碼字串，例如 "g8RsLJ"
// -----------------------------------------------------------------------------
function generateCaptchaCode(?int $length = null): string
{
    // ?? 是「null 聯合運算子」：左邊是 null 就用右邊的值。
    // 所以呼叫端沒指定長度時，就用 rand(4, 8) 隨機挑一個長度，
    // 讓每次的驗證碼長度不固定，程式更難自動破解。
    $length = $length ?? rand(4, 8);

    // 可以使用的字元表：數字 + 小寫英文 + 大寫英文，共 62 個字元。
    // 如果覺得 0/O、1/l 容易看錯，可以自行把那幾個字元從這串裡刪掉。
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // 先準備一個空字串，等一下用 .= 一個字一個字接上去。
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        // random_int() 是「加密等級」的亂數，比 rand() 更難被預測，
        // 用在驗證碼、密碼這類安全相關的地方要選它。
        // strlen($chars) - 1 是因為字串索引從 0 開始，最後一個字的索引是「長度減 1」。
        // $chars[數字] 可以像陣列一樣取出字串中的第 N 個字元。
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $code;
}


// -----------------------------------------------------------------------------
// 函式：locateFontFile()
// 功能：在系統中找一個可以用來畫字的 TrueType 字型檔
// 回傳：找到就回傳字型檔的完整路徑；全部都找不到就回傳空字串 ''
// -----------------------------------------------------------------------------
// 為什麼需要這個？
//   PHP 畫文字有兩種方式：
//     1. imagestring()  → 用 GD 內建的點陣字型，不需要字型檔，但字很小又醜
//     2. imagettftext() → 用 TrueType 字型檔（.ttf/.ttc），字大又漂亮，但必須指定字型檔路徑
//   字型檔的位置在每台電腦上不一定相同，所以這裡列出幾個常見的 Windows 字型路徑，
//   一個一個試，找到第一個存在的就用它。
//
// 如果你的環境不是 Windows（例如 Linux 主機），
// 就把候選清單改成該系統的字型路徑，例如：
//   '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
function locateFontFile(): string
{
    $fontCandidates = [
        'C:/Windows/Fonts/arial.ttf',   // Arial（小寫檔名）
        'C:/Windows/Fonts/Arial.ttf',   // Arial（大寫檔名，有些系統是這樣）
        'C:/Windows/Fonts/msyh.ttc',    // 微軟正黑體
        'C:/Windows/Fonts/simsun.ttc',  // 新細明體
    ];

    foreach ($fontCandidates as $fontFile) {
        // file_exists() 檢查這個檔案在不在，在的話就直接 return 出去，
        // return 會立刻結束整個函式，所以後面的候選字型就不會再檢查了。
        if (file_exists($fontFile)) {
            return $fontFile;
        }
    }

    // 全部都找不到 → 回傳空字串，呼叫端看到空字串就知道要改用 imagestring()。
    return '';
}


// -----------------------------------------------------------------------------
// 函式：measureCaptchaGlyphs(string $code, string $fontFile)
// 功能：替每個字元決定「旋轉角度、顏色」，並量出它旋轉之後實際佔多大
// 參數：$code     = 驗證碼文字
//       $fontFile = 字型檔路徑；空字串代表找不到字型，要退回 GD 內建字型
// 回傳：一個陣列，每個元素是一個字元的資料：
//       [
//         'char'  => 'A',   // 這個字元
//         'angle' => -7,    // 旋轉角度（度），正值向左倒、負值向右倒
//         'rgb'   => [r, g, b], // 這個字元專屬的顏色
//         'width' => 26,    // 旋轉後實際佔用的寬
//         'height'=> 31,    // 旋轉後實際佔用的高
//         'minX'  => -3,    // 繪圖原點的修正量（見下方說明）
//         'minY'  => -24,
//       ]
// -----------------------------------------------------------------------------
// 【為什麼要先量測，不能邊畫邊算？】
//   因為字元一旦旋轉，佔用的範圍就會變大（想像把一本書斜放進書架，會變寬也變高）。
//   如果沿用寫死的 180x60 畫布，斜掉的字很可能被畫布邊緣切掉。
//   所以流程必須改成三個階段：
//       第一階段：量測 → 算出每個字旋轉後要佔多少空間（就是這個函式）
//       第二階段：決定畫布大小 → 用量測結果推算需要多大，不夠就自動加大
//       第三階段：畫圖 → 在剛好夠大的畫布上把字擺好
//
// 【imagettfbbox() 與旋轉】
//   imagettfbbox($字級, $角度, $字型檔, $文字) 會「試算」文字畫出來的外框，
//   但不會真的畫。重點是它會把角度算進去，所以旋轉後的外框可以直接量到。
//   它回傳 8 個數字，是外框四個角相對於「繪圖原點」的座標：
//       [0][1] = 左下   [2][3] = 右下   [4][5] = 右上   [6][7] = 左上
//   角度 0 度時左下角就是最左最下，但一旦旋轉，「哪個角最左邊」會改變，
//   所以不能再寫成 $bbox[2] - $bbox[0]，必須把四個角的 x 拿出來取最大最小值。
function measureCaptchaGlyphs(string $code, string $fontFile): array
{
    $glyphs = [];

    // str_split() 把字串拆成一個一個字元的陣列。
    // （驗證碼只會有英數字，所以用 str_split 就夠；若要支援中文得改用 mb_str_split）
    foreach (str_split($code) as $char) {
        if ($fontFile === '') {
            // ---- 沒有字型檔的退路 ----
            // GD 內建字型（imagestring）不支援旋轉，所以角度只能是 0。
            // imagefontwidth()/imagefontheight() 可以查出內建字型每個字多大。
            $glyphs[] = [
                'char'   => $char,
                'angle'  => 0,
                'rgb'    => randomTextRgb(),
                'width'  => imagefontwidth(5),
                'height' => imagefontheight(5),
                'minX'   => 0,
                'minY'   => 0,
            ];
            continue;
        }

        // ---- 正常情況：使用 TrueType 字型 ----
        // 每個字元各自抽一個旋轉角度，範圍是 -15 ~ +15 度。
        // 逐字抽而不是整串共用一個角度，機器要辨識就得逐字校正，難度高很多。
        $angle = random_int(-CAPTCHA_MAX_ANGLE, CAPTCHA_MAX_ANGLE);

        $bbox = imagettfbbox(CAPTCHA_FONT_SIZE, $angle, $fontFile, $char);

        // 把四個角的 x 座標、y 座標分別抓出來。
        // 旋轉後不能假設哪個角在哪邊，一律用 min()/max() 找真正的邊界。
        $xs = [$bbox[0], $bbox[2], $bbox[4], $bbox[6]];
        $ys = [$bbox[1], $bbox[3], $bbox[5], $bbox[7]];

        $minX = min($xs);
        $minY = min($ys);

        $glyphs[] = [
            'char'   => $char,
            'angle'  => $angle,
            'rgb'    => randomTextRgb(),
            // 最右減最左 = 這個字旋轉後實際佔用的寬度（高度同理）
            'width'  => max($xs) - $minX,
            'height' => max($ys) - $minY,
            // minX / minY 是「外框左上角相對於繪圖原點的位移」。
            // imagettftext() 指定的座標是文字的基準點（baseline 起點），不是左上角，
            // 而且旋轉後這個位移會變。畫圖時用「想放的左上角座標 - minX/minY」
            // 就能把字精準地放到指定位置，不會有的字偏高、有的字偏左。
            'minX'   => $minX,
            'minY'   => $minY,
        ];
    }

    return $glyphs;
}


// -----------------------------------------------------------------------------
// 函式：randomTextRgb()
// 功能：隨機挑一個「文字用」的顏色
// 回傳：[r, g, b] 三個 0~255 的數字
// -----------------------------------------------------------------------------
// 為什麼把範圍限制在 0~120 而不是 0~255？
//   背景是很淡的灰藍色 (245,248,250)。如果顏色隨便抽，抽到接近白色的淺色，
//   字就會跟背景糊在一起，人眼也看不到，驗證碼就變成不可能的任務。
//   限制每個色版最大 120，可以保證抽出來的一定是「深色」，
//   但深藍、深紅、深綠、深紫都有機會出現，顏色仍然夠多變。
function randomTextRgb(): array
{
    return [random_int(0, 120), random_int(0, 120), random_int(0, 120)];
}


// -----------------------------------------------------------------------------
// 函式：randomLineRgb()
// 功能：隨機挑一個「干擾線用」的顏色
// 回傳：[r, g, b] 三個 0~255 的數字
// -----------------------------------------------------------------------------
// 干擾線的顏色範圍刻意設在 150~215（中淺色），比文字的 0~120 淡很多。
// 這是驗證碼設計最重要的平衡：
//   線太深 → 蓋住文字，人眼也認不出來，使用者會抓狂
//   線太淺 → 機器很容易把它濾掉，等於沒有防護效果
// 淡到「人腦會自動忽略、但程式仍要花力氣分離」是最理想的區間。
function randomLineRgb(): array
{
    return [random_int(150, 215), random_int(150, 215), random_int(150, 215)];
}


// -----------------------------------------------------------------------------
// 函式：randomYInQuarter(int $height, int $quarter)
// 功能：在「把圖片高度切成四等分」之後，從指定的那一等分裡隨機取一個 y 座標
// 參數：$height  = 圖片總高度
//       $quarter = 要第幾等分，0 = 最上面，3 = 最下面
// 回傳：一個 y 座標
// -----------------------------------------------------------------------------
// 為什麼要切成四等分？
//   舊版的做法是「一端在上半部、另一端在下半部」，這樣每條線都保證穿過中線，
//   結果就是所有的線全部擠在圖片中央交叉成一團，
//   反而在中間形成一個規律的密集區，機器只要避開中間就能讀到字。
//   改成四等分之後，兩端可以是任意組合（例如上到上、上到下、下到中…），
//   線的斜率與分布就散開了，整張圖找不到固定的規律。
function randomYInQuarter(int $height, int $quarter): int
{
    // intdiv() 是整數除法，會直接得到整數，不會出現小數點。
    $bandHeight = intdiv($height, 4);

    $top = $quarter * $bandHeight;

    // 最後一等分要一路吃到圖片底部。
    // 因為 $height 不見得能被 4 整除（例如 62 / 4 = 15.5），
    // 用乘法算最後一格的底部會少算幾個像素，所以直接指定成 $height - 1。
    $bottom = ($quarter === 3) ? $height - 1 : $top + $bandHeight - 1;

    return random_int($top, $bottom);
}


// -----------------------------------------------------------------------------
// 函式：createCaptchaImage(string $code)
// 功能：把驗證碼文字畫成一張圖
// 參數：$code = 要畫上去的驗證碼文字
// 回傳：一個 GD 圖片物件（還在記憶體裡，尚未輸出成檔案或網頁內容）
// -----------------------------------------------------------------------------
// 【設計重點 1】這個函式「只負責畫圖，不負責輸出」。
//   它不會送 header()，也不會 echo 任何東西，只把畫好的圖回傳出去。
//   為什麼要這樣切？因為同一張圖有兩種用途：
//     - 直接輸出成 PNG 給 <img src="xxx.php"> 用
//     - 轉成 base64 字串包在 JSON 裡給 ajax 用（本專案採用這種）
//   把「畫圖」和「輸出」分開，同一份繪圖程式碼就能同時支援兩種用法。
//
// 【設計重點 2】畫布大小是「算」出來的，不是寫死的。
//   因為每個字都會隨機旋轉，旋轉後佔的空間會變大，而且驗證碼長度本身也是
//   4~8 個字不固定，所以流程是：先量測全部字元 → 加總 → 需要多大就開多大，
//   只是不會小於 CAPTCHA_MIN_WIDTH / CAPTCHA_MIN_HEIGHT。
//   ★ 這代表每次產生的圖片尺寸可能都不一樣，
//     前端的 <img> 千萬不要寫死 width / height，否則圖會被拉扁或壓縮變形。
//
// 注意：用完這張圖之後要記得呼叫 imagedestroy() 把記憶體釋放掉。
function createCaptchaImage(string $code)
{
    $fontFile = locateFontFile();

    // ---- 步驟 1：量測 ----
    // 先決定每個字的角度與顏色，並算出它們旋轉後各自要佔多少空間。
    // 這一步完全不碰畫布，純粹是數學計算。
    $glyphs = measureCaptchaGlyphs($code, $fontFile);

    // ---- 步驟 2：由量測結果推算畫布大小 ----
    $textWidth = 0;   // 所有字元橫向排開之後的總寬度
    $textHeight = 0;  // 最高的那個字有多高

    foreach ($glyphs as $glyph) {
        $textWidth += $glyph['width'];
        // 每個字的高度不同（旋轉角度不同、字形本身也不同），
        // 畫布高度要遷就最高的那一個，否則最高的字會被切到。
        $textHeight = max($textHeight, $glyph['height']);
    }

    // 字與字之間的間隔：n 個字有 n-1 個間隔。
    // count($glyphs) - 1 在只有一個字時會是 0，剛好不會多加，不必特別判斷。
    $textWidth += CAPTCHA_CHAR_GAP * (count($glyphs) - 1);

    // 真正的畫布尺寸 = 文字需要的空間 + 兩側留白，
    // 再跟最小尺寸取大的那一個（max）。
    // 這行就是需求「自動判斷是否要加大寬度」的實作：
    //   字少又剛好都沒轉多少 → 用最小尺寸 180x60，維持版面穩定
    //   字多或轉得比較斜     → 自動長大到剛好裝得下，絕對不會切到字
    $width  = (int) max(CAPTCHA_MIN_WIDTH,  $textWidth  + CAPTCHA_PADDING_X * 2);
    $height = (int) max(CAPTCHA_MIN_HEIGHT, $textHeight + CAPTCHA_PADDING_Y * 2);

    // ---- 步驟 3：建立畫布並鋪底 ----
    // imagecreatetruecolor() 建立一張全彩的空白畫布。
    // 剛建立時整張是黑色的，所以下面要先填上背景色。
    $image = imagecreatetruecolor($width, $height);

    // imagecolorallocate() 的參數是 (圖片, 紅, 綠, 藍)，每個顏色 0~255。
    // 它會回傳一個「顏色代號」，之後畫圖時就用這個代號指定顏色。
    // 文字和干擾線的顏色改成每個都隨機，所以這裡只固定背景與外框兩色。
    $backgroundColor = imagecolorallocate($image, 245, 248, 250); // 很淡的灰藍色（背景）
    $borderColor     = imagecolorallocate($image, 220, 220, 220); // 淺灰（外框）

    // imagefill() 是「油漆桶」工具：從座標 (0,0) 開始往外填滿同色區域。
    imagefill($image, 0, 0, $backgroundColor);

    // imagerectangle() 畫一個「空心」矩形當外框。
    // 座標要減 1 是因為像素從 0 開始算，寬 180 的圖最右邊那一欄是第 179 欄。
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

    // ---- 步驟 4：逐字畫上驗證碼 ----
    // 舊版是一次把整串字丟給 imagettftext()，但那樣整串只能共用一個角度、一個顏色。
    // 要做到「每個字不同顏色、不同角度」，就必須自己算位置、一個一個畫。

    // $penX 是「筆」目前的水平位置，畫完一個字就往右移動。
    // 起點讓整串文字水平置中：(畫布寬 - 文字總寬) / 2
    $penX = (int) (($width - $textWidth) / 2);

    foreach ($glyphs as $glyph) {
        // 每個字各自垂直置中。
        // 因為每個字旋轉後的高度不一樣，若用同一個 y 會看起來高低不齊。
        $top = (int) (($height - $glyph['height']) / 2);

        // 把量測階段抽好的 RGB 值，在這張畫布上配置成可用的顏色代號。
        // 顏色一定要等畫布建立之後才能配置，所以量測時只先記下 RGB 數值。
        $color = imagecolorallocate($image, $glyph['rgb'][0], $glyph['rgb'][1], $glyph['rgb'][2]);

        if ($fontFile === '') {
            // 沒有字型檔的退路：GD 內建字型，不能旋轉但至少顏色能不同。
            imagestring($image, 5, $penX, $top, $glyph['char'], $color);
        } else {
            // imagettftext() 的座標是「繪圖原點」，不是左上角，
            // 所以要用「想放的位置 - 量測到的位移量」換算回原點座標。
            // 這就是量測階段特地把 minX / minY 記下來的原因。
            imagettftext(
                $image,
                CAPTCHA_FONT_SIZE,
                $glyph['angle'],          // 這個字專屬的旋轉角度
                $penX - $glyph['minX'],
                $top - $glyph['minY'],
                $color,                   // 這個字專屬的顏色
                $fontFile,
                $glyph['char']
            );
        }

        // 筆往右移動「這個字的寬度 + 間隔」，準備畫下一個字。
        $penX += $glyph['width'] + CAPTCHA_CHAR_GAP;
    }

    // ---- 步驟 5：加干擾線 ----
    // 干擾線的目的是讓程式難以自動辨識文字，但又要讓人眼還看得懂，
    // 所以線的顏色要比文字淡，數量也不能太多。
    $lineCount = random_int(CAPTCHA_LINE_MIN, CAPTCHA_LINE_MAX);

    // 左右各 15% 的 x 畫素帶，確保每條線都橫跨整個字元區域。
    // 例如寬 180 時：左端點落在 0~27、右端點落在 152~179。
    $bandWidth    = (int) ($width * CAPTCHA_EDGE_BAND_RATIO);
    $leftBandMax  = $bandWidth;
    $rightBandMin = $width - 1 - $bandWidth;

    for ($i = 0; $i < $lineCount; $i++) {
        // 每條線各自決定粗細：1 或 2。
        // 粗細不一致，機器就不能用「固定線寬」這個特徵來過濾干擾線。
        // imagesetthickness() 是「狀態設定」，設定之後畫的線都套用這個粗細，
        // 所以每一圈都要重設一次。
        imagesetthickness($image, random_int(1, 2));

        // 每條線各自配一個顏色，機器也不能用「固定顏色」來把線挑掉。
        $lineRgb = randomLineRgb();
        $lineColor = imagecolorallocate($image, $lineRgb[0], $lineRgb[1], $lineRgb[2]);

        // 起點固定在最左邊那一帶、終點固定在最右邊那一帶，
        // 這樣線一定會從左穿到右，不會只在角落畫一小段沒有干擾效果。
        $x1 = random_int(0, $leftBandMax);
        $x2 = random_int($rightBandMin, $width - 1);

        // 兩端的 y 各自從「四等分」中隨機挑一等分再取座標。
        // 兩端可以落在同一等分（線接近水平），也可以一上一下（線很斜），
        // 所以線的斜率、經過的位置都很分散，不會像舊版那樣全部擠在中間交叉。
        $y1 = randomYInQuarter($height, random_int(0, 3));
        $y2 = randomYInQuarter($height, random_int(0, 3));

        // imageline() 畫直線，參數是 (圖片, 起點x, 起點y, 終點x, 終點y, 顏色)
        imageline($image, $x1, $y1, $x2, $y2, $lineColor);
    }

    // 畫完把粗細改回 1，避免影響之後可能加上的其他繪圖動作。
    imagesetthickness($image, 1);

    // 把畫好的圖回傳給呼叫端，由呼叫端決定要怎麼輸出。
    return $image;
}


// -----------------------------------------------------------------------------
// 函式：captchaImageToDataUri(string $code)
// 功能：把驗證碼畫成圖，再轉成可以直接放進 <img src="..."> 的 base64 字串
// 參數：$code = 要畫上去的驗證碼文字
// 回傳：形如 "data:image/png;base64,iVBORw0KGgo..." 的字串
// -----------------------------------------------------------------------------
// 【什麼是 data URI？】
//   平常 <img src="photo.png"> 是叫瀏覽器「再連一次伺服器去下載那個檔案」。
//   data URI 則是把圖片本身的資料直接寫在 src 裡面，
//   瀏覽器看到就直接畫出來，不用再發一次請求。
//   格式：data:[MIME型別];base64,[圖片二進位資料轉成的 base64 文字]
//
//   缺點是 base64 會讓資料量變大約 1.33 倍，
//   但驗證碼圖很小（大約 3KB），而且這樣才能包在 JSON 裡用 ajax 傳，很划算。
//
// 【為什麼要用「輸出緩衝區」？】
//   PHP 的 imagepng() 很固執：它只會把 PNG 資料「印出去」（送給瀏覽器），
//   沒有辦法叫它「回傳成一個變數」。
//   所以這裡用一個技巧：
//     ob_start()     → 先把水龍頭接上水桶，之後印出來的東西都流進水桶裡
//     imagepng()     → 它以為自己印給瀏覽器了，其實是印進水桶
//     ob_get_clean() → 把水桶裡的內容倒出來變成變數，同時把水桶收掉
//   這樣就成功把 PNG 的二進位內容「接」進 $binary 變數了。
function captchaImageToDataUri(string $code): string
{
    $image = createCaptchaImage($code);

    ob_start();                 // 開始攔截輸出
    imagepng($image);           // PNG 資料被攔截下來，不會送到瀏覽器
    $binary = ob_get_clean();   // 取出攔截到的內容，並關閉攔截

    // GD 圖片會佔用記憶體，用完一定要釋放。
    imagedestroy($image);

    // base64_encode() 把二進位資料轉成「只有英數字和 +/= 的純文字」，
    // 因為 JSON 和 HTML 屬性都只能放文字，不能直接放二進位資料。
    return 'data:image/png;base64,' . base64_encode($binary);
}


// -----------------------------------------------------------------------------
// 函式：captchaIssueCode(bool $forceNew = false)
// 功能：取得目前這位使用者的驗證碼答案（順便存進 Session）
// 參數：$forceNew  false = 沿用舊的（沒有才產生新的）
//                  true  = 強制換一組新的，這就是前端說的「重置 / reload」
// 回傳：驗證碼字串
// -----------------------------------------------------------------------------
// 【為什麼預設要「沿用舊的」？】
//   因為使用者可能會重新整理頁面、或前端不小心重複呼叫了發圖服務。
//   如果每次呼叫都換一組新答案，使用者照著螢幕上的圖輸入卻永遠是錯的
//   （因為 Session 裡的答案已經被換掉了），這是新手很常踩到的坑。
//   所以只有明確要求 reload 時才換新的。
function captchaIssueCode(bool $forceNew = false): string
{
    captchaStartSession();

    // empty() 同時涵蓋「這一格根本不存在」和「存在但是空字串」兩種情況，
    // 比只用 isset() 更保險。
    if ($forceNew || empty($_SESSION[CAPTCHA_SESSION_KEY])) {
        $_SESSION[CAPTCHA_SESSION_KEY] = generateCaptchaCode();
    }

    return $_SESSION[CAPTCHA_SESSION_KEY];
}


// -----------------------------------------------------------------------------
// 函式：captchaVerify(string $input)
// 功能：比對使用者輸入的答案跟 Session 裡的正確答案
// 參數：$input = 使用者從前端輸入送來的字串
// 回傳：true = 正確；false = 不符合
// -----------------------------------------------------------------------------
function captchaVerify(string $input): bool
{
    captchaStartSession();

    // ?? '' 是防呆：萬一 Session 過期了、或使用者根本沒先去要過驗證碼，
    // 這一格會不存在。直接讀不存在的鍵值會發出警告，所以用 ?? 給它一個預設空字串。
    $expected = $_SESSION[CAPTCHA_SESSION_KEY] ?? '';

    // trim() 去掉頭尾的空白。使用者常常會不小心多打一個空格，
    // 或從別的地方複製貼上時帶到空白，這種情況不該算他錯。
    $input = trim($input);

    // 只要有一邊是空的就直接判定失敗。
    // 特別重要的是 $expected === '' 這個檢查：
    // 如果沒有它，使用者送一個空字串來，就會變成「空 === 空」而通過驗證，
    // 等於整個驗證碼形同虛設，這是很嚴重的安全漏洞。
    if ($expected === '' || $input === '') {
        return false;
    }

    // hash_equals() 是專門用來比對機密字串的函式。
    // 它跟 === 的差別在於「比對時間固定」：
    //   用 === 比字串時，第一個字就不同會馬上回傳 false，全部相同才慢慢比到最後，
    //   攻擊者可以精密測量回應時間，一個字一個字猜出答案（這叫 timing attack）。
    //   hash_equals() 不管對錯都花一樣久，就沒有這個線索可以利用。
    // 對驗證碼來說風險其實不高，但養成好習慣沒有壞處。
    if (CAPTCHA_CASE_INSENSITIVE) {
        // 要忽略大小寫，就把兩邊都轉成小寫再比。
        // 注意不能改用 strcasecmp()，那個函式沒有固定時間的保護。
        return hash_equals(strtolower($expected), strtolower($input));
    }

    return hash_equals($expected, $input);
}
