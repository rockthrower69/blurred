<?php
	$cats = array_diff(scandir('pictures'), array('.', '..'));
	$cat = $cats[array_rand($cats)];
	$imgs = array_diff(scandir('pictures/'.$cat), array('.', '..'));
	$img = $imgs[array_rand($imgs)];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Blurred.io</title>
	<link rel="stylesheet" type="text/css" href="normalize.css">
	<link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
</head>

<body>

	<div id="blurcontainer">
		<span>Drag over the square to reveal the image pixel by pixel. Whenever you think to recognize something, write it in the textbox below.</span>
		<span>Speed mode (magnifies instantly until deepest pixel) <input type="checkbox" name="speedmode" id="speedmode"></span>
		<canvas id="blurred"></canvas>
		<div id="answers">
			<input type="text" maxlength="1">
			<div style="clear:both;"></div>
		</div>
		<div class="section group" id="stats">
			<div class="col span_6_of_12" id="zooms">
				<i class="fa fa-search-plus"></i>
				<span>0 zooms</span>
			</div>
			<div class="col span_6_of_12" id="erases">
				<i class="fa fa-eraser"></i>
				<span>0 erases</span>
			</div>
		</div>
	</div>
	<style type="text/css">
		html,
		body {
			width: 100%;
			height: 100%;
			margin: 0;
			padding: 0;
			font: 12px Verdana;
		}


/*  SECTIONS  */
.section {
	clear: both;
	padding: 0px;
	margin: 0px;
}

/*  COLUMN SETUP  */
.col {
	display: block;
	float:left;
	margin: 1% 0 1% 2%;
	box-sizing: border-box; /* Fkin padding */
}
.col:first-child { margin-left: 0; }

/*  GROUPING  */
.group:before,
.group:after { content:""; display:table; }
.group:after { clear:both;}
.group { zoom:1; /* For IE 6/7 */ }
/*  GRID OF TWELVE  */
.span_12_of_12 {
	width: 100%;
}

.span_11_of_12 {
  	width: 91.5%;
}
.span_10_of_12 {
  	width: 83%;
}

.span_9_of_12 {
  	width: 74.5%;
}

.span_8_of_12 {
  	width: 66%;
}

.span_7_of_12 {
  	width: 57.5%;
}

.span_6_of_12 {
  	width: 49%;
}

.span_5_of_12 {
  	width: 40.5%;
}

.span_4_of_12 {
  	width: 32%;
}

.span_3_of_12 {
  	width: 23.5%;
}

.span_2_of_12 {
  	width: 15%;
}

.span_1_of_12 {
  	width: 6.5%;
}

/*  GO FULL WIDTH BELOW 480 PIXELS */
@media only screen and (max-width: 480px) {
	.col {  margin: 1% 0 1% 0%; }
    
    .span_1_of_12, .span_2_of_12, .span_3_of_12, .span_4_of_12, .span_5_of_12, .span_6_of_12, .span_7_of_12, .span_8_of_12, .span_9_of_12, .span_10_of_12, .span_11_of_12, .span_12_of_12 {
	width: 100%; 
	}
}

		
		#blurcontainer {
			width: 514px;
			margin: 50px auto;
		}
		
		#blurred {
			background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuMTnU1rJkAAAABlBMVEW/v7////+Zw/90AAAAEUlEQVQI12P4z8CAFWEX/Q8Afr8P8erzE9cAAAAASUVORK5CYII=');
			display: block;
			margin: 0 auto;
			border: 1px solid #ddd;
		}

		#answers {
			box-sizing: border-box; /* Fkin padding */
			margin-top: 8px;
			width: 100%;
			border: 1px solid #ddd;
		}

		#answers > input {
			outline: none;
			color: transparent;
			text-shadow: 0 0 0 black;
			font-size: 120%;
			padding: 4px 0;
			transition: all 500ms ease-in-out;
			border: 0;
			width: 16px;
			text-transform: uppercase;
			text-align: center;
			float: left;
			cursor: pointer;
		}

		#answers > input:focus {
			background: #ddd;
			border-bottom: 2px solid #ddd !important;
		}

		#stats {
			margin-top: 4px;
		}

		#stats > div {
			border: 1px solid #ddd;
			padding: 6px 8px;
		}
	</style>
	<script type="text/javascript" src="jquery-3.2.1.min.js"></script>
	<script type="text/javascript">
		var Blurred = {
			Game: {
				canvas: $('#blurred'),
				ctx: $('#blurred')[0].getContext('2d'),
				answer: null,
				image: null,
				imagedata: null,
				progressdata: null,
				size: 0,
				zoomCount: 0,
				eraseCount: 0,
				init() {
					Blurred.Game.events.init();
				},
				load(answer, url) {
					Blurred.Game.answer = answer;
					Blurred.Game.image = new Image();
					Blurred.Game.image.src = url;
					Blurred.Game.image.crossOrigin = '';
					Blurred.Game.image.onload = Blurred.Game.loaded;
				},
				loaded() {
					/*if(image.width != image.height) {
						return console.error('Image should be square');
					}
					if((image.width & (image.width - 1)) != 0) {
						return console.error('Image should be a power of 2');
					}
					var size = image.width;
					var maxdepth = Math.log2(size);
					var canvas = document.createElement('canvas');
					canvas.width = canvas.height = size;
					canvas.imageSmoothingEnabled= false;
					var ctx = canvas.getContext('2d');
					ctx.drawImage(image, 0, 0);*/
					Blurred.Game.size = Math.pow(2, Math.round(Math.log2(Math.min(Math.max(Blurred.Game.image.width, Blurred.Game.image.height), 512))));
					var ratio = Blurred.Game.size / Math.max(Blurred.Game.image.width, Blurred.Game.image.height);
					Blurred.Game.canvas[0].width = Blurred.Game.canvas[0].height = Blurred.Game.size;
					Blurred.Game.canvas[0].imageSmoothingEnabled = false;
					Blurred.Game.ctx.drawImage(Blurred.Game.image, (Blurred.Game.size - Blurred.Game.image.width * ratio) / 2, (Blurred.Game.size - Blurred.Game.image.height * ratio) / 2, Blurred.Game.image.width * ratio, Blurred.Game.image.height * ratio);

					Blurred.Game.imagedata = Blurred.Game.ctx.getImageData(0, 0, Blurred.Game.size, Blurred.Game.size).data;

					Blurred.Game.progressdata = [Blurred.Game.average(0, 0, Blurred.Game.size), Blurred.Game.average(0, 0, Blurred.Game.size), Blurred.Game.average(0, 0, Blurred.Game.size), Blurred.Game.average(0, 0, Blurred.Game.size)];
					Blurred.Game.ctx.clearRect(0, 0, Blurred.Game.size, Blurred.Game.size);
					Blurred.Game.ctx.fillStyle = Blurred.Game.progressdata;
					Blurred.Game.ctx.fillRect(0, 0, Blurred.Game.size, Blurred.Game.size);
				},
				average(offsetx, offsety, avgsize) {
					var color = [0, 0, 0, 0];
					for (var x = offsetx; x < offsetx + avgsize; x++) {
						for (var y = offsety; y < offsety + avgsize; y++) {
							color[0] += Blurred.Game.imagedata[4 * (y * Blurred.Game.size + x)];
							color[1] += Blurred.Game.imagedata[4 * (y * Blurred.Game.size + x) + 1];
							color[2] += Blurred.Game.imagedata[4 * (y * Blurred.Game.size + x) + 2];
							color[3] += Blurred.Game.imagedata[4 * (y * Blurred.Game.size + x) + 3];
						}
					}
					for (var i in color) {
						color[i] /= Math.pow(avgsize, 2);
						color[i] = Math.round(color[i]);
					}
					color[3] /= 255;
					return 'rgba(' + color.join(',') + ')';
				},
				focus(x, y) {
					if(x<0 || y<0 || x>Blurred.Game.size || y>Blurred.Game.size) {
						return;
					}
					var maxdepth = Math.log2(Blurred.Game.size);
					var depth = 0;
					var part = Blurred.Game.progressdata; // Part is reference of avgdata
					var offsetx = 0,
						offsety = 0;
					while (depth < maxdepth) {
						var posx = parseInt(x.toString(2).padStart(maxdepth + 1, '0').substr(depth, 1));
						var posy = parseInt(y.toString(2).padStart(maxdepth + 1, '0').substr(depth, 1));
						var pos = posx + posy * 2;
						if (typeof(part[pos]) == 'string') {
							var size = Math.pow(2, maxdepth - depth) / 2;
							offsetx += Math.pow(2, maxdepth - depth) * posx;
							offsety += Math.pow(2, maxdepth - depth) * posy;
							part[pos] = [Blurred.Game.average(offsetx, offsety, size), Blurred.Game.average(offsetx + size, offsety, size), Blurred.Game.average(offsetx, offsety + size, size), Blurred.Game.average(offsetx + size, offsety + size, size)];

							$('#zooms span').text(String(++Blurred.Game.zoomCount).replace(/(.)(?=(\d{3})+$)/g,'$1\.'));
							for (var i = 0; i < 4; i++) {
								Blurred.Game.ctx.clearRect(offsetx + (i % 2) * size, offsety + (i > 1) * size, size, size);
								Blurred.Game.ctx.fillStyle = Blurred.Game.average(offsetx + (i % 2) * size, offsety + (i > 1) * size, size);
								Blurred.Game.ctx.fillRect(offsetx + (i % 2) * size, offsety + (i > 1) * size, size, size);
							}
							console.log($('#speedmode').is(':checked') && depth < maxdepth);
							if ($('#speedmode').is(':checked') && depth < maxdepth) {
								Blurred.Game.focus(x, y);
							}
							break;
						} else {
							part = part[pos];
							depth++;
							offsetx += Math.pow(2, maxdepth - depth + 1) * posx;
							offsety += Math.pow(2, maxdepth - depth + 1) * posy;
						}
					}
				},
				events: {
					isDragging: false,
					init() {
						Blurred.Game.canvas.on('mousedown', function(e) {
							Blurred.Game.focus(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
							Blurred.Game.events.isDragging = true;
						}).on('mousemove', function(e) {
							if(Blurred.Game.events.isDragging) {
								e.preventDefault();
								Blurred.Game.focus(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
							}
						}).on('touchmove', function(e) {
							e.preventDefault();
							for (var i = 0; i < e.targetTouches.length; i++) {
								Blurred.Game.focus(Math.floor(e.touches[i].pageX - this.offsetLeft), Math.floor(e.touches[i].pageY - this.offsetTop));
							}
						});

						$(document).on('mouseup', function(e) {
							Blurred.Game.events.isDragging = false;
							if($(e.target).is(':not(#answers input)')) {
								$('#answers input').last().focus();
							}
						});

						$('#answers').on('keydown', 'input', function(e) {
							console.log(e.originalEvent);
							var key = e.originalEvent.key;
							if(key.length == 1 && !e.originalEvent.ctrlKey && !e.originalEvent.altKey) {
								if($(this).val().length == 0) {
									$(this).val(key);
									$('<input type="text" maxlength="1">').insertAfter(this);
									$('#answers input').last().focus();
								}else{
									$(this).val(key);
									$(this).next().focus();
								}
								e.preventDefault();
							}else if(key == 'Backspace'){
								if($(this).prev().length) {
									$(this).prev().remove();
									$('#erases span').text(String(++Blurred.Game.eraseCount).replace(/(.)(?=(\d{3})+$)/g,'$1\.'));
								}
								e.preventDefault();
							}else if(key == 'Delete'){
								if($(this).next().is('input') && $(this).next().val().length > 0) {
									$(this).next().remove();
									$('#erases span').text(String(++Blurred.Game.eraseCount).replace(/(.)(?=(\d{3})+$)/g,'$1\.'));
								}
								e.preventDefault();
							}else if(key.toLowerCase() == 'v' && e.originalEvent.ctrlKey) {
								console.log(this, e);
							}else if(key == "ArrowLeft") {
								$(this).prev().focus();
								e.preventDefault();
							}else if(key == "ArrowRight") {
								$(this).next().focus();
								e.preventDefault();
							}
						});

						$('#answers').on('keyup', 'input', function(e) {
							var a = '';
							$('#answers > input').each(function(i) {
								var c = $(this).val().toLowerCase();
								a += c;
								if(c.length == 1 && Blurred.Game.answer.charAt(i) == c) {
									$(this).css({borderBottom: '2px solid #4caf50'});
								}else if(c.length == 1 && Blurred.Game.answer.indexOf(c) !== -1) {
									$(this).css({borderBottom: '2px solid #ff9800'});
								}else if(c.length == 1) {
									$(this).css({borderBottom: '2px solid #e01c42'});
								}else {
									$(this).css({borderBottom: '0'});
								}
							});
							if(a == Blurred.Game.answer) {
								alert('feest');
							}
						});


						/* document.getElementById('answer').addEventListener('keyup', function(e) {
							if(this.value.indexOf(answer) !== -1) {
								this.style.boxShadow = 'rgb(42, 195, 48) 0px 0px 10px';
							}else{
								var answers = answer.split(' ');
								var c = false;
								for(var i in answers) {
									if(this.value.indexOf(answers[i]) !== -1) {
										c = true;
										break;
									}
								}
								this.style.boxShadow = (c ? 'rgba(37, 255, 0, 0.5) 0px 0px 10px' : '0 0 10px rgba(255, 0, 0, 0.5)');
							}
						}); */
					}
				}
			}
		}
		Blurred.Game.init();
		Blurred.Game.load(atob('<?php echo base64_encode(str_replace('-', ' ', ltrim(strstr($cat, '.'), '.'))); ?>'), 'pictures/'+atob('<?php echo base64_encode($cat.'/'.$img); ?>'));
	</script>
</body>

</html>