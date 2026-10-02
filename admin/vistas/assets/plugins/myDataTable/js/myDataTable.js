var typingTimer = window.typingTimer || null;
var delay = 500;
var arregloDataTable = window.arregloDataTable || new Object();

function ajaxMyDatatable(data) {
	if (!data || !data.table || !data.table.length) {
		return;
	}

	var numTh = data.table.children('thead').children('tr').children('th').length;
	if (numTh != data.colums.length) {
		console.error("Error MyDataTable: El número de columnas no coincide con el arreglo dado en #" + data.table.attr('id') + ". Encabezados <th>: " + numTh + ", data.colums: " + data.colums.length);
		return;
	}

	Object.assign(arregloDataTable, { [data.table.attr('id')]: data });

	if (data.table.children('thead').children('tr').children('th.sorting').length == 0) {
		if (data.sort != undefined) {
			data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').children('i').removeClass('fa-sort');
			data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').addClass('sorting');
			data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').children('i').css('color', '#007AC4');

			if (data.sort[1] == "asc") {
				data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').children('i').addClass('fa-sort-up');
				data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').addClass('sorting_asc');
			} else if (data.sort[1] == "desc") {
				data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').children('i').addClass('fa-sort-down');
				data.table.children('thead').children('tr').children('th:eq(' + data.sort[0] + ')').addClass('sorting_desc');
			}
		} else {
			data.table.children('thead').children('tr').children('th:eq(0)').children('i').removeClass('fa-sort');
			data.table.children('thead').children('tr').children('th:eq(0)').children('i').addClass('fa-sort-up');
			data.table.children('thead').children('tr').children('th:eq(0)').addClass('sorting');
			data.table.children('thead').children('tr').children('th:eq(0)').addClass('sorting_asc');
			data.table.children('thead').children('tr').children('th:eq(0)').children('i').css('color', '#007AC4');
		}
	} else {
		data.sort[0] = data.table.children('thead').children('tr').children('th.sorting').index();
		if (data.table.children('thead').children('tr').children('th.sorting').hasClass("sorting_asc")) {
			data.sort[1] = "asc";
		} else if (data.table.children('thead').children('tr').children('th.sorting').hasClass("sorting_desc")) {
			data.sort[1] = "desc";
		}
	}

	data.table.children('thead').children('tr').children("th[orden='No']").children('i').remove();

	var paginaSele = $(".paginasMyDataTable.active[tabla=" + data.table.attr('id') + "]").length;
	if (paginaSele == 0) {
		paginaSele = 1;
	} else {
		paginaSele = parseInt($(".paginasMyDataTable.active[tabla=" + data.table.attr('id') + "]").text());
	}

	Object.assign(data.params, { "buscar": $(".buscadorMyDataTable[tabla=" + data.table.attr('id') + "]").val() });
	Object.assign(data.params, { "limit": $(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val() });
	Object.assign(data.params, { "pagina": paginaSele });
	Object.assign(data.params, { "ordenColumna": data.colums[data.sort[0]] });
	Object.assign(data.params, { "orden": data.sort[1] });

	$.ajax({
		url: data.url,
		type: 'POST',
		data: data.params,
		beforeSend: function () {
			$("#carga").show();
		}
	})
		.done(function (res) {
			try {
				var resA = (typeof parseRespuesta === 'function') ? parseRespuesta(res) : JSON.parse(String(res).replace(/[\uFEFF]/g, '').trim());
				if (!resA || typeof resA !== 'object' || Array.isArray(resA)) {
					resA = { data: [], totales: { NumRows: 0 } };
				}

				if (resA.data != undefined && resA.data.length > 0) {
					data.table.children('tbody').html('');

					for (var i = 0; i < resA.data.length; i++) {
						var columnas = "";
						for (var x = 0; x < data.colums.length; x++) {
							columnas += '<td>' + resA.data[i][data.colums[x]] + '</td>';
						}

						data.table.children('tbody').append('<tr id="' + resA.data[i]['ID'] + '">' + columnas + '</tr>');
					}

					if (data.totals != undefined) {
						var footer = "";

						data.table.children('thead').children('tr').children('th').each(function (index, el) {
							if (data.totals[$(this).index()] != undefined) {
								footer += "<th>" + resA.totales[data.totals[$(this).index()]] + "</th>";
							} else {
								footer += "<th></th>";
							}
						});

						data.table.children('tfoot').html("<tr>" + footer + "</tr>");
					}

					var numPaginas = parseInt(resA.totales.NumRows) / parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val());
					var residuo = parseInt(resA.totales.NumRows) % parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val());
					if (residuo > 0) {
						numPaginas = ((parseInt(resA.totales.NumRows) - residuo) / parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val())) + 1;
					}

					var paginas = "", disabled1 = "", disabled2 = "", numIni = 1, numFin = 5;
					if (numPaginas <= 10) {
						numFin = numPaginas;
					}

					if (paginaSele >= 5 && numPaginas > 10) {
						paginas += '<button type="button" class="btn btn-outline-secondary btn-sm paginasMyDataTable" tabla="' + data.table.attr('id') + '">1</button>';
						paginas += '<button type="button" class="btn btn-outline-secondary btn-sm" disabled>...</button>';

						numIni = paginaSele - 1;
						numFin = paginaSele + 1;

						if ((numPaginas - paginaSele) < 5) {
							numFin = numPaginas;
							numIni = numPaginas - 5;
						}
					}

					for (var i = numIni; i <= numFin; i++) {
						if (i == paginaSele) {
							if (i == 0) {
								disabled1 = "disabled";
							} else if (i == numPaginas) {
								disabled2 = "disabled";
							}

							paginas += '<button type="button" class="btn btn-outline-primary btn-sm paginasMyDataTable active" tabla="' + data.table.attr('id') + '">' + i + '</button>';
						} else {
							paginas += '<button type="button" class="btn btn-outline-secondary btn-sm paginasMyDataTable" tabla="' + data.table.attr('id') + '">' + i + '</button>';
						}
					}

					if (numPaginas > 10 && (numPaginas - paginaSele) >= 5) {
						paginas += '<button type="button" class="btn btn-outline-secondary btn-sm" disabled>...</button>';
						paginas += '<button type="button" class="btn btn-outline-secondary btn-sm paginasMyDataTable" tabla="' + data.table.attr('id') + '">' + numPaginas + '</button>';
					}

					if (numPaginas == 1) {
						disabled1 = "disabled";
						disabled2 = "disabled";
					}

					$("#" + data.table.attr('id') + "_Pagination").remove();

					var desde = (((paginaSele * parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val())) - parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val())) + 1);
					var hasta = (paginaSele * parseInt($(".numRowsMyDataTable[tabla=" + data.table.attr('id') + "]").val()));
					var total = parseInt(resA.totales.NumRows);

					if (hasta > total) {
						hasta = total;
					}

					data.table.parent().parent().append(`<div class="row align-items-center justify-content-between g-2 mt-2 px-1" id="` + data.table.attr('id') + `_Pagination">
					<div class="col-12 col-md-auto text-center text-md-start">
						<p class="mb-0 text-muted" style="font-size: 13px;">Mostrando registros del `+ new Intl.NumberFormat('en-US').format(desde) + ` al ` + new Intl.NumberFormat('en-US').format(hasta) + ` de un total de ` + new Intl.NumberFormat('en-US').format(total) + ` registros</p>
					</div>
					<div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-end">
						<div class="btn-toolbar">
							<div class="btn-group">
								<button type="button" class="btn btn-outline-secondary btn-sm prePagButton" `+ disabled1 + `>Ant.</button>
							    `+ paginas + `
							    <button type="button" class="btn btn-outline-secondary btn-sm nextPagButton" `+ disabled2 + `>Sig.</button>
							</div>
						</div>
					</div>
				</div>`);

					moneda();
				} else {
					data.table.children('tbody').html(`<tr>
					<td colspan="`+ data.colums.length + `">No existen registros.</td>
				</tr>`);
					data.table.children('tfoot').html("");
					$("#" + data.table.attr('id') + "_Pagination").remove();
				}
			} catch (error) {
				console.error("Error MyDataTable: " + error);
				data.table.children('tbody').html('<tr><td colspan="' + data.colums.length + '">No existen registros.</td></tr>');
			}
		})
		.fail(function () {
			console.log("Error ajax MyDataTable");
		}).always(function () {
			$("#carga").hide();
		});
}

function crearDataTable() {

	$(".myDataTable").each(function (index, el) {
		if($(this).hasClass('creada')){
			return;
		}
		
		$(this).addClass('creada');
		var padre = $(this).parent();
		if (padre.hasClass('table-responsive')) {
			padre.removeClass('table-responsive');
		}
		var tabla = padre.children('table.myDataTable');
		var id = tabla.attr('id');

		tabla.children('thead').children('tr').children('th').each(function (index, el) {
			$(this).append('<i class="fas fa-sort"></i>')
		});

		tabla.children('tbody').html(`<tr>
			<td colspan="`+ tabla.children('thead').children('tr').children('th').length + `">Cargando...</td>
		</tr>`);

		var tablaHtml = padre.html();

		$(this).remove();

		padre.append(`<div class="row align-items-center justify-content-between g-2 mb-3">
				<div class='col-auto'>
					<div class="d-flex align-items-center gap-2">
						<select class="form-select form-select-sm numRowsMyDataTable" tabla="`+ id + `" style="width: auto;">
						  	<option value="10">10</option>
						  	<option value="25" selected>25</option>
						  	<option value="50">50</option>
						  	<option value="100">100</option>
						  	<option value="250">250</option>
						</select>
						<span class="text-muted small d-none d-sm-inline">registros</span>
					</div>
				</div>
				<div class='col-auto ms-auto'>
					<div class="input-group input-group-sm">
					  	<span class="input-group-text bg-white" style="color: #909090;"><i class="fas fa-search"></i></span>
					  	<input type="text" class="form-control form-control-sm buscadorMyDataTable" tabla="`+ id + `" placeholder="Buscar..." style="max-width: 220px;">
					</div>
				</div>
			</div>
			<div class="table-responsive w-100 mb-2">
				`+ tablaHtml + `
			</div>`);
	});
}

jQuery(document).ready(function ($) {
	$(document).on('click', '.myDataTable thead tr th', function () {
		if ($(this).attr('orden') != 'No') {
			var num = $(this).index();
			$(this).addClass('sorting');
			$(this).children('i').css('color', '#007AC4');

			if ($(this).children('i').hasClass('fa-sort')) {
				$(this).children('i').removeClass('fa-sort');
				$(this).children('i').addClass('fa-sort-up');
				$(this).addClass('sorting_asc');
			} else if ($(this).children('i').hasClass('fa-sort-up')) {
				$(this).children('i').removeClass('fa-sort-up');
				$(this).children('i').addClass('fa-sort-down');
				$(this).removeClass('sorting_asc');
				$(this).addClass('sorting_desc');
			} else if ($(this).children('i').hasClass('fa-sort-down')) {
				$(this).children('i').removeClass('fa-sort-down');
				$(this).children('i').addClass('fa-sort-up');
				$(this).removeClass('sorting_desc');
				$(this).addClass('sorting_asc');
			}

			$(this).parent().children('th').each(function (index, el) {
				if (index != num) {
					$(this).removeClass('sorting_asc');
					$(this).removeClass('sorting_desc');
					$(this).removeClass('sorting');
					$(this).children('i').css('color', '');
					$(this).children('i').removeClass('fa-sort-up');
					$(this).children('i').removeClass('fa-sort-down');
					$(this).children('i').addClass('fa-sort');
				}
			});

			ajaxMyDatatable(arregloDataTable[$(this).parent().parent().parent().attr('id')]);
		}
	});

	$(document).on('change', '.numRowsMyDataTable', function () {
		var tabla = $(this).attr('tabla');

		$(".paginasMyDataTable").each(function (index, el) {
			$(this).removeClass('active');
			$(this).removeClass('btn-primary');
			$(this).addClass('btn-outline-secondary');
		});

		$(".paginasMyDataTable:eq(0)").addClass('active');
		$(".paginasMyDataTable:eq(0)").removeClass('btn-outline-secondary');
		$(".paginasMyDataTable:eq(0)").addClass('btn-primary');

		ajaxMyDatatable(arregloDataTable[tabla]);
	});


	$(document).on('keyup', '.buscadorMyDataTable', function (event) {

		if ($(this).attr('tabla') === 'tablaBuscarProductos' || $(this).attr('tabla') === 'tablaVentasPendientes' || $(this).attr('tabla') === 'tablaBuscarClientes') {
			var regex = new RegExp("^[a-zA-Z0-9]+$");
			var key = event.key;
			const code = event.keyCode ?? event.which;
			/*console.log({ key })
			console.log(regex.test(key))*/

			if ((code < 48 || code > 105 || [91, 92, 93].includes(code)) && code !== 8) {
				return false;
			}
		}
		var tabla = $(this).attr('tabla');
		$(".paginasMyDataTable").each(function (index, el) {
			$(this).removeClass('active');
			$(this).removeClass('btn-primary');
			$(this).addClass('btn-outline-secondary');
		});

		$(".paginasMyDataTable:eq(0)").addClass('active');
		$(".paginasMyDataTable:eq(0)").removeClass('btn-outline-secondary');
		$(".paginasMyDataTable:eq(0)").addClass('btn-primary');

		clearTimeout(typingTimer); // Reiniciar temporizador
		
		typingTimer = setTimeout(function () {
			ajaxMyDatatable(arregloDataTable[tabla]);
		}, delay);
	});

	$(document).on('keydown', '.buscadorMyDataTable', function () {
        clearTimeout(typingTimer); // Detener temporizador si vuelve a escribir
    });

	$(document).on('click', '.paginasMyDataTable', function () {
		var tabla = $(this).attr('tabla');

		$(".paginasMyDataTable").each(function (index, el) {
			$(this).removeClass('active');
			$(this).removeClass('btn-primary');
			$(this).addClass('btn-outline-secondary');
		});

		$(this).addClass('active');
		$(this).removeClass('btn-outline-secondary');
		$(this).addClass('btn-primary');

		ajaxMyDatatable(arregloDataTable[tabla]);
	});

	$(document).on('click', '.prePagButton', function () {
		var num = $(this).parent().children('button.active').index();
		$(this).parent().children('button:eq(' + (num - 1) + ')').trigger('click');
	});

	$(document).on('click', '.nextPagButton', function () {
		var num = $(this).parent().children('button.active').index();
		$(this).parent().children('button:eq(' + (num + 1) + ')').trigger('click');
	});

	function between(x, min, max) {
		return x >= min && x <= max;
	}
});