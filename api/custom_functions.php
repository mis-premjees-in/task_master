<?php
		// Write you custom functions in here: 
		//You can call this functions using the api_functions API: provide your function_name and the parameters in the request body.
		// Example:
		// {
		//     "username": "admin",
		//     "access_token": "your_access_token",
		//     "function_name": "get_table_data",
		//     "parameters": ["table_name", "select_fields", "custom_where", "page_number", "results_per_page"]
		// }
		// When writing your functions ensure you return a value that can be converted to JSON. For example, an array or an object or string.
		
		function test_function($username="John Doe",$age="20"){
			return "Hello $username, you are $age years old";
		}