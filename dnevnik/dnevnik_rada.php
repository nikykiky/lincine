


<?php require_once("../sigurnost/sigurnosniKod.php"); ?>

<!DOCTYPE html>
<html>
<head>
	
    <meta charset="utf-8">
    <title>Dnevnik rada</title>
    <link href="dnevnik_radacss.css" rel="stylesheet" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

</head>
<body>
	
<div class="digital-clock" id="digitalClock"></div>
    <div class="sve">
	
	
	
			<a href="../ucenik/admin_ucenika.php" id="AmdinA">Admin </a>			

		<?php 
			//trenutno samo botun odjava
			require_once("../izbornik.php"); 
		?> 

		<h2>Dnevnik rada</h2>

		<p id="demo"></p>
		<div class="unos_dnevnika">
			<form action="" method="POST"> 
				<input type="text" name="id_korisnika" value="<?=$_SESSION['user_id']?>" style="display:none"/>
				Opis: <br />
				<textarea rows="3" cols="5" name="opis_dnevnik_rada"></textarea>
				<br />
				<input type="submit" value="Dodaj dnevnik rada" name="sbmt_dnevnik_rad"/>
			</form>

			<div>
				<input type="text" id="datepicker">		
				
			</div>
			<div class="clock">
			  
			</div>
		</div>
		
		<h2>Pregled dnevnika rada za današnji datum</h2>
<?php
	
	require_once("../sigurnost/sigurnosniKod.php");
	
	// Create a database connection
	$con = mysqli_connect("localhost", "root", "", "dnevnik_rada_psiholog");
	
	// Check the connection
	if (mysqli_connect_error()) {
		die("Database connection failed: " . mysqli_connect_error());
	}
	
	if (isset($_POST['sbmt_dnevnik_rad'])) {
		$korisnik = $_SESSION['user_id'];
		$opis = $_POST["opis_dnevnik_rada"];
	
		// Use prepared statement to insert data
		$stmt = $con->prepare("INSERT INTO dnevnik_rada (id_ko, opis) VALUES (?, ?)");
		$stmt->bind_param("is", $korisnik, $opis);
	
		if ($stmt->execute()) {
			header("Location: " . $_SERVER['PHP_SELF']);
			exit();
		} else {
			echo "Error: " . $stmt->error;
		}
	}
	
	$danasnji_datum = date("Y-m-d");
	$pdtc_dnevnik_rada = mysqli_query($con, "
		SELECT * FROM dnevnik_rada
		INNER JOIN korisnik ON korisnik.id_ko = dnevnik_rada.id_ko
		WHERE datum_unosa LIKE '$danasnji_datum%'
	");
	
    echo "<table id='tablica_dnevnika_rada' border='1'>
            <tr id='plava' valign='top'>
            <td width='50%'><b>Dnevnik rada</b></td>
            <td width='20%'><b>Upisao</b></td>
            <td width='15%'><b>Izmjeni</b></td>
            <td width='15%'><b>Obrisi</b></td>
            </tr>";

    while ($redak = mysqli_fetch_array($pdtc_dnevnik_rada)) {
        $id = $redak['id_dr'];
        $dt = new DateTime($redak['datum_unosa']);
        $vrijeme = $dt->format('H:i');

        echo "<tr valign='top'><td>";
        echo $redak['opis'];
        echo "</td><td>";
        echo $redak['ime'] . " " . $vrijeme;
        echo "</td><td>";
        echo "<a onclick='uredi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_opis='$redak[opis]' data-dr_id='$id'>Uredi</a>";
        echo "</td><td>";
        echo "<a onclick='izbrisi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_id='$id'>Izbrisi</a>";
        echo "</td></tr>";
    }
    echo "</table>";
    ?>
</div>

<div id="dialog" title="Uredivanje unosa" style="display: none;">
    <textarea id="uredi_unos" style="height:100%;padding:5px; font-family:Sans-serif; font-size:1.2em;"></textarea>
    <input type="text" style="display: none" />
</div>
    </div>
	</div>




<script>      
	
	/** 
	 * neki sat vjerojatno za delete al ajd
	*/
	
	

/**
	 * samom sat
	 */
	
 
	function izbrisi_unos_iz_dnevnika(obj) {
		var id_delete_dnevnika_rada = obj.getAttribute('data-dr_id');
		//console.log(id_delete_dnevnika_rada);
		
		//treba mi ajax amsterdam jer ne mogu napisati sql upit koji u sebi sadrzi js varijabli (ne ide nikako klijentski i serverski jezik skupa)
		if (confirm("Jesi sigurna :/") == true) {
			$.ajax({
				type: "POST",
				url: "sql_izbrisi_iz_dnevnika.php",
				data: {"id_unosa_za_brisanje" : id_delete_dnevnika_rada},
				success: function (rez) {
					location.reload(); 
				}
			});
		}
	}



	$("#dialog").dialog({
		autoOpen: false,
		height: 400,
		width: 450,
		modal: true,
		resizable: true,
		buttons: {
			"Unesi": function() {
				var unos = $('#dialog').find("textarea").val();
				var id_unosa_za_edit = $('#dialog').find("input").val();
				$.ajax({
					type: "POST",
					url: "spremi_editirani_unos_iz_dnevnika.php",
					data: {"opis_dnevnik_rada" : unos, "id_unosa_za_edit" : id_unosa_za_edit },
					success: function (rez) {
						location.reload(); 
					}
				});
				$(this).dialog("close");
			},
			"Odustani": function() {
				$(this).dialog("close");
			}
		}
	});

	function uredi_unos_iz_dnevnika(obj) {
		var opis_dnevnika_rada = obj.getAttribute('data-dr_opis');
		var id_unosa_za_edit = obj.getAttribute('data-dr_id');
		$('#dialog').find("textarea").val(opis_dnevnika_rada);
		$('#dialog').find("input").val(id_unosa_za_edit);
		$('#dialog').dialog('open');
	}


	$("#datepicker").datepicker({
    dateFormat: "mm-dd-yy",
    onSelect: function (dateText, inst) {
        var odabrani_datum = new Date(dateText);
        var datum = odabrani_datum.getFullYear() + '-' + ((odabrani_datum.getMonth() + 1) < 10 ? '0' : '') + (odabrani_datum.getMonth() + 1) + '-' + ((odabrani_datum.getDate() + 1) < 10 ? '0' : '') + (odabrani_datum.getDate());

        var url = 'sql_dohvati_po_datumu.php';
        $.post(url, { datum: datum }, function (data) {
            $('#demo').empty(); // Clear previous content
            if (data === "Nista nije uneseno taj dan") {
                alert("Nema unosa za odabrani datum.");
            } else {
                $('#demo').append(data);
                // Process the data and update the table here
                var tbody = $("#tablica_dnevnika_rada tbody");
                tbody.empty(); // Clear previous content
                var noviRedak = "";
                data = JSON.parse(data);
                console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var redak = data[i];
                    noviRedak +=
                        `<tr>
                            <td>${redak.opis}</td>
                            <td>${redak.datum_unosa}</td> 
                            <td><a onclick='uredi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_opis='${redak.opis}' data-dr_id='${redak.id}'>Uredi</a></td>
                            <td><a onclick='izbrisi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_id='${redak.id}'>Izbriši</a></td>
                        </tr>`;
                    console.log(redak);
                    tbody.append(noviRedak);
                }
            }
        });

        $.ajax({
            type: "POST",
            url: "sql_dohvati_po_datumu.php",
            data: { "datum": datum },
            success: function (podaci) {
                var tbody = $("#tablica_dnevnika_rada tbody");
                tbody.empty(); // Clear previous content
                var noviRedak = "";
                podaci = JSON.parse(podaci);
                console.log(podaci);

                for (var i = 0; i < podaci.length; i++) {
                    var redak = podaci[i];
                    noviRedak +=
                        `<tr>
                            <td>${redak.opis}</td>
                            <td>${redak.datum_unosa}</td> 
                            <td><a onclick='uredi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_opis='' data-dr_id=''>NERADIII</a></td>
                            <td><a onclick='izbrisi_unos_iz_dnevnika(this)' style='text-decoration: underline; cursor: pointer' data-dr_id=''>NERADIII</a></td>
                        </tr>`;
                    console.log(redak);
                    tbody.append(noviRedak);
                }
            }
        });
    }
});

			

	var danasnjiDatum = new Date();
	var dan = danasnjiDatum.getDate();
	var mjesec = danasnjiDatum.getMonth() + 1; // Mjeseci kreću od 0
	var godina = danasnjiDatum.getFullYear();
	// Formatirajte datum prema vašim željama (npr., "dd.mm.yyyy")
	var formatiraniDatum =  mjesec + '-' + dan + '-' + godina;
	// Postavite vrijednost input polja na današnji datum
	$("#datepicker").val(formatiraniDatum);




</script>

<script>
		
	function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const digitalClock = document.getElementById('digitalClock');
    digitalClock.textContent = hours + ':' + minutes + ':' + seconds;
  }
setInterval(updateClock, 1000);


  updateClock();

	</script>
</body>
</html>
