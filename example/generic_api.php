<?php

/*
 * This is a simple script demonstrating how to create a generic API.
 * Deploy this anywhere.
 *
 * It is protected by a secret key, which is transmitted in plaintext.
 * You need to change this to something secure and random, e.g. using https://www.grc.com/passwords.htm
 *
 * Once deployed, you can request it like so: http://my.server.example.com/generic_api.php?key=your_secret_key
 */

if ($_GET['key'] !== "your_secret_key") {
	throw new Exception("Invalid key.");
}

// now you have to do the hard work: define how you calculate your balance to return.
$balance = 1.0115;

// now print it out as a normal number
echo $balance;

// that's it!
