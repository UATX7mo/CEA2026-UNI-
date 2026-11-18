<?php
require_once("encabezado.php");
?>
<br><br>
    <div class="container">
    
            <form method="post" action="validar.php">
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Email address</label>
                <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="user">
                <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" name="pass">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            </form>
           
    </div>
    <br><br>
<?php
require_once("pie.php");
?>