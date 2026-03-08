<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地下偶像入口網站</title>
    <link rel="stylesheet" href="./sass/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="stylesheet" href="project01.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        $(function(){
            // 在 id 為 "nav-placeholder" 的地方載入 navbar.html
            $("#nav-placeholder").load("navbar.php");
        });
    </script>
    
</head>

<body>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="searchToast" class="toast align-items-center text-white bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    💡 <b>請善用 Ctrl + F</b> 進行搜索
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <div id="nav-placeholder"></div>
    <div class="container mt-4">
        <div class="p-4">
            <p>資料來源: 
                <a href="https://zh.wikiversity.org/wiki/%E5%8F%B0%E7%81%A3%E5%9C%B0%E4%B8%8B%E5%81%B6%E5%83%8F%E6%BC%94%E5%87%BA%E8%80%85">維基學院-台灣地下偶像演出者</a>
                <a href=""></a>
            </p>


        </div>
    </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>





    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 移除 localStorage 的判斷，直接設定延遲彈出
            setTimeout(function () {
                // 抓取 ID: searchToast
                var toastEl = document.getElementById('searchToast');

                // 初始化並顯示 Toast
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: false // 設定不會自動消失，直到點擊 X
                });
                toast.show();
            }, 2000); // 2秒後顯示
        });
    </script>


</body>

</html>