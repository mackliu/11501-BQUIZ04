<?php
// =============================================================================
// 檔案：captcha_image.php
// 用途：【服務一】發放圖形驗證碼 —— 產生一組驗證碼並回傳圖片
// 方法：只接受 GET
// 格式：回傳 JSON，圖片以 base64 的 data URI 夾帶在裡面
// =============================================================================
//
// ■ 使用說明 ────────────────────────────────────────────────────────────────
//
//   1) 取得驗證碼（沿用目前 Session 裡的那一組，沒有的話才產生新的）
//         GET  captcha_image.php
//
//   2) 重置驗證碼（使用者按「換一張」時用，一定會換成全新的一組）
//         GET  captcha_image.php?reload=1
//
//   成功時回傳（HTTP 200）：
//         {
//           "success": true,
//           "image"  : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
//           "length" : 6
//         }
//
//   失敗時回傳（例如用 POST 來呼叫，HTTP 405）：
//         {"success":false,"message":"只接受 GET 請求。"}
//
//   欄位說明：
//     success ── 布林值，這次請求成功與否
//     image   ── 可以直接指定給 <img> 的 src 屬性，瀏覽器會自動解碼並顯示
//     length  ── 這組驗證碼有幾個字。只是拿來給前端做提示用
//                （例如 maxlength、或顯示「請輸入 6 個字元」），
//                答案本身「絕對不會」出現在回傳內容裡
//
//   ★ 圖片尺寸不是固定的！
//     字元會隨機旋轉、驗證碼長度也是 4~8 字不固定，後端會依實際需要自動加大畫布，
//     所以前端的 <img> 不要寫死 width / height，否則圖會被拉扁或壓縮變形。
//     建議只用 max-width: 100% 限制它不要超出容器就好。
//
// ■ 前端呼叫範例 ─────────────────────────────────────────────────────────────
//
//   // 用 fetch（現代寫法）
//   async function loadCaptcha(reload = false) {
//       const url = reload ? 'captcha_image.php?reload=1' : 'captcha_image.php';
//       const response = await fetch(url, { credentials: 'same-origin' });
//       const data = await response.json();
//       if (data.success) {
//           document.getElementById('captchaImage').src = data.image;
//       }
//   }
//
//   ★ credentials: 'same-origin' 很重要！
//     驗證碼的答案存在 Session，而瀏覽器是靠 Cookie 裡的 PHPSESSID
//     才知道自己是哪一位使用者。加上這個選項，fetch 才會把 Cookie 一起送出去。
//     少了它，每次請求對伺服器來說都是「新的陌生人」，
//     會一直拿到不同的驗證碼，驗證永遠不會過。
//
// ■ 為什麼用 base64 而不是直接輸出 PNG？────────────────────────────────────
//
//   傳統做法是 <img src="captcha.php">，讓瀏覽器直接去下載一張 PNG。
//   改成 base64 包在 JSON 裡的好處是：
//     - 可以在同一個回應裡順便帶其他資訊（像這裡的 length，或未來的錯誤訊息）
//     - 前端完全用 ajax 控制什麼時候換圖，不必靠「在網址後面加亂數騙過快取」
//     - 換圖時不會有圖片閃一下空白的情形
//   代價是資料量變大約 1.33 倍，但驗證碼圖只有幾 KB，影響很小。
// =============================================================================


// 載入共用函式庫。裡面有產碼、繪圖、Session 處理的所有邏輯。
// 用 require_once 而不是 include：
//   require = 載入失敗就直接中止程式（這個檔案沒有它根本無法運作，早點停掉比較好抓錯）
//   _once   = 就算被載入多次也只會真正執行一次，避免「函式重複定義」的錯誤
require_once 'captcha_lib.php';


// -----------------------------------------------------------------------------
// 送出 HTTP 回應標頭（header）
// -----------------------------------------------------------------------------
// header() 一定要在「任何內容被輸出之前」呼叫，否則會出現
// "headers already sent" 的錯誤。這也是為什麼本檔案第一個字元就是 <?php，
// 前面連一個空格或換行都不能有。

// 告訴瀏覽器：接下來的內容是 JSON、編碼是 UTF-8。
// 前端的 response.json() 才會正確解析，中文訊息也才不會變亂碼。
header('Content-Type: application/json; charset=utf-8');

// 告訴瀏覽器和中間的代理伺服器：這個回應「絕對不要快取」。
// 沒有這幾行的話，瀏覽器可能會覺得「這個網址我剛剛才要過」，
// 直接把上次的結果拿出來用，導致按了「換一張」畫面卻沒變。
// Cache-Control 是給現代瀏覽器看的，Pragma 是為了相容很舊的瀏覽器。
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');


// -----------------------------------------------------------------------------
// 檢查請求方法：這支服務只接受 GET
// -----------------------------------------------------------------------------
// $_SERVER['REQUEST_METHOD'] 會是 'GET'、'POST'、'PUT' 等字串。
// 明確擋掉非預期的方法，是後端服務的基本紀律：
// 讓呼叫端一眼看出自己用錯方式，而不是得到一個看起來正常卻不對的結果。
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // http_response_code() 設定 HTTP 狀態碼。
    // 405 的意思是 "Method Not Allowed"（方法不被允許）。
    // 回傳正確的狀態碼，前端才能用 response.ok 判斷成敗。
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只接受 GET 請求。'], JSON_UNESCAPED_UNICODE);
    exit; // 立刻結束，後面的程式一行都不要執行
}


// -----------------------------------------------------------------------------
// 判斷這次是不是「重置（reload）」
// -----------------------------------------------------------------------------
// 網址帶了 ?reload=1 就代表使用者按了「換一張」，要丟掉舊答案重新產生一組。
//
// 這裡的判斷條件看起來有點囉嗦，是因為要排除三種「其實不算重置」的情況：
//   isset(...)              → 網址根本沒有 reload 這個參數
//   $_GET['reload'] !== '0' → 有人寫成 ?reload=0，明顯是想關掉這個功能
//   $_GET['reload'] !== ''  → 有人寫成 ?reload=（等號後面空的），意圖不明，當作沒帶
// 三個條件都通過，才確定使用者真的要換一張。
$forceNew = isset($_GET['reload']) && $_GET['reload'] !== '0' && $_GET['reload'] !== '';


// -----------------------------------------------------------------------------
// 產生（或取回）驗證碼，然後畫成圖回傳
// -----------------------------------------------------------------------------
// captchaIssueCode() 會處理 Session：
//   $forceNew = true  → 一定產生新的一組，覆蓋掉 Session 裡的舊答案
//   $forceNew = false → Session 裡已經有答案就沿用，沒有才產生新的
// 回傳的 $code 是「正確答案」，只在伺服器這一端使用，不會傳給前端。
$code = captchaIssueCode($forceNew);

// json_encode() 把 PHP 陣列轉成 JSON 字串。
// JSON_UNESCAPED_UNICODE 這個選項是讓中文維持原樣，
// 不然中文會被轉成 只接 這種跳脫碼，功能上沒問題但很難閱讀與除錯。
echo json_encode([
    'success' => true,

    // captchaImageToDataUri() 會把 $code 畫成 PNG，再轉成 base64 的 data URI。
    // 注意這裡送出去的是「圖片」，不是答案本身。
    'image'   => captchaImageToDataUri($code),

    // 只回傳長度給前端當提示。
    // ★ 千萬不要一時手滑寫成 'code' => $code，那等於把答案直接送給前端，
    //   任何人打開瀏覽器的開發者工具就能看到答案，驗證碼就完全失去意義了。
    'length'  => strlen($code),
], JSON_UNESCAPED_UNICODE);

// 明確結束程式。這個檔案到這裡任務就完成了，
// 寫上 exit 可以確保之後就算有人不小心在檔案尾巴加了東西，也不會混進 JSON 裡。
exit;
