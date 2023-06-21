<?php
//submitted from join form?



// get parameter email, name


// check if email, name in db ?

//yes?  error



//no?

//insert into db, create logintoken, send email, confirm message


?>

<!DOCTYPE html>
<html>
<body>

<form action="/" method="post" enctype="multipart/form-data">
  Join:  <br>
  <label for="email">Email:
  <input type="text" name="email" id="email">
  </label>

  <br>

  <label for="name">Nickname:
  <input type="text" name="name" id="name">
  </label>
  
  <br>
  
  <input type="submit" value="submit" name="Join">
</form>

</body>
</html>