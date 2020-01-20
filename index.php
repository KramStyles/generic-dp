<!doctype html>
<html ⚡>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <link rel="stylesheet" href="css/generic_dp.css" type="text/css">
    <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/generic_dp.js"></script>
    <title>
      Generic DP Demo
    </title>
  </head>
  <body>
    <div class="" id="implort-here" style="width:500px; margin:0 auto">

    </div>
  </body>
  <script type="text/javascript">
    $("document").ready(function(){
      $("#implort-here").generic_dp({
      	src: "assets/c_icon.png",
      	size: "300x300",
      	backend: "upload_v2_0.php",
      	Folder_p : "assets/cropped/",
      	Folder_w : "assets/cropped/",
      	complete : function (res) {
          console.log(res);
        }
      });
    })
  </script>
</html>
