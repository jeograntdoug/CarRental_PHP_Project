<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>
    <body>
        <script type="text/javascript">
            $(document).ready(function () {
                var conn = new WebSocket('ws://localhost:8080');
                conn.onopen = function (e) {
                    console.log("Connection established!");
                }
                conn.onmessage=function (e) {
                    console.log(e.data);
                }
            })
        </script>
    </body>
</html>