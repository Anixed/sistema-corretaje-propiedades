							<?php require('connect-mysql.php'); ?>
							<?php
							if ( empty($form['id_propiedad']) ) {
								$form['id_propiedad'] = $_POST['id_propiedad'];
							}
							$sql = "SELECT id, imagen_real, imagen_thumb, nombre_imagen FROM propiedades_fotos
									WHERE id_propiedad=".$form['id_propiedad']." ORDER BY id DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							if ($result->num_rows > 0) {
								while ($row = $result->fetch_array()) {
									echo '<a href="'.$row['imagen_real'].'" class="open-foto">
									<img src="'.$row['imagen_thumb'].'" id="foto-'.$row['id'].'" title="'.$row['nombre_imagen'].'" width="190" />
									<span>'.$row['nombre_imagen'].'</span>
									</a>';
								}
								$result->close();
							} else {
								echo '<img src="images/no-image.png" id="sin-image" title="Propiedad sin fotografías" width="190" />';
							}
							?>