<?php

require("db.php");

// if post, fire changes
if ($_SERVER['REQUEST_METHOD'] == "POST") {

	/* Begin a transaction, turning off autocommit */
	$dbh->beginTransaction();

	// try find an replace
	if ($_REQUEST['action'] != "query") {

		// prepare
	    $stmt = $dbh->prepare("UPDATE quicksearchdata SET {$_REQUEST['col_search']} = REPLACE({$_REQUEST['col_search']}, :find_string, :replace_string) WHERE INSTR({$_REQUEST['col_search']}, :find_string) > 0");    
	    $stmt->bindParam(':find_string', $_REQUEST['find_string']);
	    $stmt->bindParam(':replace_string', $_REQUEST['replace_string']);
	    
	    // execute      
	    $stmt->execute();

	    // msg    
		$affect_count = $stmt->rowCount();
		$results_msg = "$affect_count rows would be affected.";	
		$show_rollback_form	 = 1;
		$results_background_color = "#FFF2CC";

		// hide form
		$fp_show = "hidden";
	}

	// try rollback
	if ($_REQUEST['action'] == "rollback") {
		
		// rollback
		$dbh->rollBack();

		$results_msg = "Changes rolled back.";
		$show_rollback_form = 0;
		$results_background_color = "#D9EAD3";
		$fp_show = "show";
	}

	// commit rollback
	if ($_REQUEST['action'] == "commit") {
		
		// rollback
		$dbh->commit();

		$results_msg = "Changes committed!";
		$show_rollback_form = 0;
		$results_background_color = "#D9EAD3";
		$fp_show = "show";
	}

    // reveal results and hide form
    $results_show = "show";    

}


// offer rollback that would switch the find and replace...

else {
	$fp_show = "show";
	$results_show = "hidden";	
}

?>

<html>

	<head>
		<link rel="stylesheet" href="skeleton/css/skeleton.css">
		<link rel="stylesheet" href="skeleton/css/normalize.css">
		<style>
			.margin20 {
				margin-top: 20px;			
			}
			.hidden {
				display: none;
			}
			.show {
				display: block;
			}
			.results {				
				background-color:<?php echo $results_background_color; ?>;
				padding:30px;
				border-radius:10px;
			}
		</style>

	</head>

	<body>

		<!-- .container is main centered wrapper -->
		<div class="container">

			
			<div class="row">
				<div class="twelve column">
					<h2>QuickSearch Manage</h2>
				</div>
			</div>

			<!-- database model overview -->
			<div class="row">
				<div class="twelve columns">
					<div class="margin20"></div>
					<img class="u-max-full-width" src="img/Quicksearch_DB_Model.png"/>
					<div class="margin20"></div>
				</div>
			</div>		

			<div class="margin20"></div>

			<!-- Utilities -->
			<div class="row">				

				<!-- find and replace by column in quicksearch.quicksearchdata table -->
				<div class="twelve columns <?php echo $fp_show; ?>">
					<h4>Find and Replace by Column</h4>
					<!-- The above form looks like this -->
					<form action="." method="POST">
						<div class="row">
							<div class="six columns">
								<label for="col_search">Column to Search in 'quicksearchdata' Table</label>
								<select class="u-full-width" type="text" placeholder="foo" name="col_search" id="col_search">
									<option value="resource_url">Resource URL (resource_url)</option>
									<option value="link_text">Link Text (link_text)</option>
									<option value="search_term">Original Search Terms (search_term)</option>
									<option value="click_category">Click Category (click_category)</option>									
								</select>
							</div>
						</div>
						<div class="row">
							<div class="six columns">
								<label for="find_string">String to Find</label>
								<input class="u-full-width" type="text" placeholder="foo" name="find_string" id="find_string">
							</div>														
							<div class="six columns">
								<label for="replace_string">String to Replace With</label>
								<input class="u-full-width" type="text" placeholder="bar" name="replace_string" id="replace_string">
							</div>
						</div>
						<div class="margin20"></div>
						<input type="hidden" name="rollback" id="rollback" value="0">
						<input class="button-primary" type="submit" value="Submit">
					</form>
				</div>
			</div>

			<div class="margin20"></div>

			<!-- Results -->
			<div id="results" class="row <?php echo $results_show; ?>" style="">				
				<div class="twelve columns results">
					<h4>Results</h4>
					<p id="results_msg"><?php echo $results_msg; ?></p>

					<!-- hidden form for rollback -->
					<?php
					if ($show_rollback_form == 1) {
					?>
						<form action="." method="POST">					
							<input type="hidden" name="col_search" id="col_search" value="<?php echo $_REQUEST['col_search']; ?>">
							<input type="hidden" name="find_string" id="find_string" value="<?php echo $_REQUEST['replace_string']; ?>">
							<input type="hidden" name="replace_string" id="replace_string" value="<?php echo $_REQUEST['find_string']; ?>">
							<input type="hidden" name="action" id="action" value="rollback">
							<input class="button-primary" type="submit" value="Rollback?">							
						</form>
						<form action="." method="POST">					
							<input type="hidden" name="action" id="action" value="commit">
							<input class="button-primary" type="submit" value="Commit!">							
						</form>
					<?php
					}
					?>
				</div>				
			</div>

			<div class="margin20"></div>


			<div class="row">
				<!-- fires python script to reindex -->
				<div class="twelve columns">
					<h4>Reindex Data</h4>
					<p>Scripts to index Apache logs fires nightly at 11pm.  Click below to manually reindex now.</p>					
					<button class="button-primary" type="button" onclick="fireSolrIndex();">Reindex</button>				
				</div>
			</div>

		</div>

	</body>

	<!-- le js -->
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">

		function fireSolrIndex(){
			$.ajax({
				url: "http://digital.library.wayne.edu/solr4/quicksearch/dataimport",
					data: { 
				        "command": "full-import", 
				        "commit": "true", 
				        "indent": "true",
				        "wt": "json"
				    },
				}).done(function(results) {
					$("#results_msg").html(results);
					$("#results").toggleClass('hidden','show');
				});
		}

	</script>

</html>










