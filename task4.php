<?php

	for ($i = 1; $i <= 100; $i++) {
	  
	  if(($i%3 == 0) && ($i%5 == 0)) 
		echo nl2br("FizzBuzz \n");
	  else if($i%3 == 0)
		echo nl2br("Fizz \n");
	  else if($i%5 == 0) 
		echo nl2br("Buzz \n");
	  else 
		echo nl2br($i . "\n");
	  
	}
?>