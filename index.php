<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Upload Large File With Chunks</title>
	<style type="text/css">
		body {
			padding: 20px
		}

		form {
			padding: 40px 20px;
			background: #FFA0A0;
			border-radius: 10px;
		}

		form p {
			font-size: 18px;
		}

		.loading {
			position: absolute;
			display: inline-block;
			width: 16px;
			top: 1px;
			left: 4%;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>

<body>
	<h1>Upload Large File With Chunks</h1>
	<form method="">
		<p>Please select a file</p>
		<div class="file_container">
			<p>
				<input type="file" id="file" class="file">
			</p>
			<p>
				<button type="button" class="btn btn-primary" id="btn_upload" disabled><span class="glyphicon glyphicon-arrow-up"></span> Upload Large File</button>
				<button type="button" class="btn btn-primary" id="btn_merge_file" disabled><span class="glyphicon glyphicon-compressed"></span> Merge Now</button>
			</p>
		</div>
		<div id="message_info"></div>
	</form>
	<script>
		var success;
		$(document).ready(function() { //$(document).ready berfungsi untuk memastikan kode tidak berjalan sebelum doc berhasil load
			$('#file').change(function() {
				collectDataChunk();
			});
			$('#btn_upload').click(function() {
				var file_val = $('.file').val(); //mendapatkan value dari tag input dengan id val
				if (file_val == "") { //apabila val kosong
					alert("Please select a file");
					return false;
				} else {
					ajax_file_upload(file_obj, 0);
				}

			});
			$('#btn_merge_file').click(function() {
				mergeFile(file_obj);
			});
		});
		var file_obj;
		const BYTES_PER_CHUNK = 1024 * 1024; // mengatur ukuran pecahan data
		var slices;
		var totalSlices;
		var ajax;
		var formdata;
		var chunk;
		var data_chunk = [];

		function collectDataChunk() {
			file_obj = document.getElementById('file').files[0]; //mendapatkan isi dari input dengan index 0 
			if (!file_obj) { //apabila file objek ksosng
				alert("Select a file please..");
			} else {
				var start = 0;
				var end;
				var index = 0;
				slices = Math.ceil(file_obj.size / BYTES_PER_CHUNK); //membulatkan hasil dari ukuran file / ukuran data yang sudah ditentukan dan hasilnya menjadi jumlah bagian pecahan file obj
				totalSlices = slices;

				while (start < file_obj.size) {
					end = start + BYTES_PER_CHUNK;
					if (end > file_obj.size) {
						end = file_obj.size;
					}
					/*collecting chunk's data and store it */
					data_chunk[index] = start + "|" + end;
					console.log("start : " + start + ", end : " + end + ", total slices : " + totalSlices + ", slices : " + slices);
					start = end;
					index++;
					slices--;
				}
				$('#btn_upload').removeAttr("disabled");

			}
		}

		function ajax_file_upload(file_obj, f) { //funct memecahan blob
			if (file_obj != undefined) {
				var text = data_chunk[f].split("|");
				var start_ = text[0];
				var end_ = text[1];

				chunk = file_obj.slice(start_, end_); //pemotongan file obj
				var formdata = new FormData();
				formdata.append("file", chunk); //pemberian nama pad file yang sudah dipecah
				formdata.append("name", file_obj.name);
				formdata.append("index", f);
				$.ajax({
					type: 'POST',
					url: 'upload.php',
					contentType: false,
					processData: false,
					data: formdata,
					beforeSend: function(response) {
						$('#message_info').html("Uploading your file, please wait...");
					},
					success: function(response) {
						console.log("response" + response);
						if (response == 1) {
							if (f < data_chunk.length - 1) {
								sleep(300); /* give 0.3 sec to avoid error server getting busy..*/
								ajax_file_upload(file_obj, f + 1);
								$('#message_info').html("Uploading your file " + f + ", please wait...");
							} else {
								$('#message_info').html("Your file has uploaded successfully..<br>Please hits <b>Merge Now button</b> to merge your file");
								$('#btn_merge_file').removeAttr("disabled");
							}

						} else if (response == 0) {
							$('#message_info').html("Failed to upload file..").css("color", "red");
						}
					},
					error: function(xhr, textStatus, error) {
						if (textStatus == "error") {
							$('#message_info').html("Error, Something happen..").css("color", "red");
						}

					}
				});

			}
		}

		function mergeFile(file_obj) {
			var formdata = new FormData();
			formdata.append("name", file_obj.name);
			formdata.append("index", totalSlices);
			$.ajax({
				type: 'POST',
				url: 'merge.php',
				contentType: false,
				processData: false,
				data: formdata,
				beforeSend: function(response) {
					$('#message_info').html("Merging your file, please wait...");
				},
				success: function(response) {
					console.log("response" + response);
					if (response == 1) {
						$('#message_info').html("Your file has merged successfully.");
					} else if (response == 0) {
						//
					}
				},
				error: function(xhr, textStatus, error) {
					if (textStatus == "error") {
						//
					}

				}
			});
		}

		function sleep(milliseconds) {
			const date = Date.now();
			let currentDate = null;
			do {
				currentDate = Date.now();
			} while (currentDate - date < milliseconds)
		}
	</script>
</body>

</html>