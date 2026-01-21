<?php
    include '../action/user_information.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Table User Information</title>
    <meta http-equiv="refresh" content="20">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" type="text/css" href="../asset_style/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../asset_style/font-awesome/css/font-awesome.min.css" />

    <script type="text/javascript" src="../asset_style/js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="../asset_style/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">

<div class="page-header">
    <h1>Table User Information</h1>
</div>
<h3> 
<a href="../form_page/form_input_nama.php">Form Input Db</a>  | 
<a href="../action/input_nama.php">Form Input Device</a>  | 
<a href="../action/hapus_user.php">Hapus User</a>  |  
<a href="../index.php">Back to Home</a>

</h3><br>
<!-- Simple Login - START -->
<!-- you need to include the shieldui css and js assets in order for the charts to work -->
<link rel="stylesheet" type="text/css" href="https://www.shieldui.com/shared/components/latest/css/light/all.min.css" />
<script type="text/javascript" src="https://www.shieldui.com/shared/components/latest/js/shieldui-all.min.js"></script>

<div class="container" id="container1">
    
<table class="table table-striped">
<?php $no=1;?>
  <thead>
    <tr>
        <th scope="col">No</th>
        <th scope="col">User ID</th>
        <th scope="col">Nama</th>
        <th scope="col">Password</th>
        <th scope="col">Group</th>
        <th scope="col">Privilege</th>
        <th scope="col">Card</th>
        <th scope="col">ID 2</th>
        <th scope="col">T1</th>
        <th scope="col">T2</th>
        <th scope="col">T3</th>
    </tr>
  </thead>
  <tbody>
    <?php 
        for ($i = 1; $i < count($buffer)-1; $i++) {
            $data=Parse_Data($buffer[$i],"<Row>","</Row>");
            $PIN=Parse_Data($data,"<PIN>","</PIN>");
            $Name=Parse_Data($data,"<Name>","</Name>");
            $Password=Parse_Data($data,"<Password>","</Password>");
            $Group=Parse_Data($data,"<Group>","</Group>");
            $Privilege=Parse_Data($data,"<Privilege>","</Privilege>");
            $Card=Parse_Data($data,"<Card>","</Card>");
            $PIN2=Parse_Data($data,"<PIN2>","</PIN2>");
            $TZ1=Parse_Data($data,"<TZ1>","</TZ1>");
            $TZ2=Parse_Data($data,"<TZ2>","</TZ2>");
            $TZ3=Parse_Data($data,"<TZ3>","</TZ3>");
    ?>
    <tr>
        <th scope="col"><?=$i?></th>
        <th scope="col"><?=$PIN?></th>
        <th scope="col"><?=$Name?></th>
        <th scope="col"><?=$Password?></th>
        <th scope="col"><?=$Group?></th>
        <th scope="col"><?=$Privilege?></th>
        <th scope="col"><?=$Card?></th>
        <th scope="col"><?=$PIN2?></th>
        <th scope="col"><?=$TZ1?></th>
        <th scope="col"><?=$TZ2?></th>
        <th scope="col"><?=$TZ3?></th>

    </tr>
    <?php
        } 
    ?>
  </tbody>
</table>
    <div class="footer"><br>
        <p>&copy; ACM 2022</p>
    </div>            
    
</div>

<style>
    #container1 {
        background-color: #B0C4DE;
    }

    #colorDiv {
        background-color: #666666;
        height: 500px;
        margin-top: 50px;
        margin-bottom: 50px;
        border-radius: 15px;
    }

    .footer {
        text-align: center;
    }

    .footer a {
        color: #d9d9d9;
    }
</style>

<script type="text/javascript">
    $(function () {
        $('#submitBtn').shieldButton();

        $('#dropdown').shieldDropDown({
            cls: "large"
        });
    });
</script>
<!-- Simple Login - END -->

</div>

</body>
</html>