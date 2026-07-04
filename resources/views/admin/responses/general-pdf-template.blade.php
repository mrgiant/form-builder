<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<style>
             body { font-family: DejaVu Sans, sans-serif; }


			.wrapper {
				margin: 0 -20px 0;
				padding: 0 15px;
			}
		    .middle {
		        text-align: center;
		    }
		    .title {
			    font-size: 35px;
		    }
		    .pb-10 {
		    	padding-bottom: 10px;
		    }
		    .pb-5 {
		    	padding-bottom: 7px;
		    }
		    .head-content{
		    	padding-bottom: 4px;
		    	border-style: none none ridge none;
		    	font-size: 18px;
		    }
            thead { display: table-header-group; }
            tfoot { display: table-row-group; }
            tr { page-break-inside: avoid; }
		    table.table {
		    	font-size: 13px;
		    	border-collapse: collapse;
		    }
			.page-break {
		        page-break-after: always;
		        page-break-inside: avoid;
			}
			tr.even {
				background-color: #eff0f1;
			}
			table .left {
				text-align: left;
			}
			table .right {
				text-align: right;
			}
			table .bold {
				font-weight: 600;
			}
			.bg-black {
				background-color: #000;
			}
			.f-white {
				color: #fff;
			}

            td {
          white-space: normal !important;
          word-wrap: break-word;
        }
        table {
          table-layout: fixed;
        }

        .table td, .table th {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table-bordered, .table-bordered td, .table-bordered th {
    border: 1px solid #dee2e6;
}



		</style>
	</head>
	<body>

		<div class="wrapper">

		    <div class="content">
		    	<table width="100%" class="table table-bordered">
                    <thead>
                      <tr>
                        @foreach($questions as $q)
                        <th title="{{$q}}" style="font-weight: bold;">{{$q}}</th>
                        @endforeach
                        <th style="font-weight: bold;">Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($form->responses as $response)
                      <tr>

                        @foreach($questions as $qid=>$q)

                        <td>{{ $data[$response->id][$qid]}}</td>
                        @endforeach
                        <td title='{{$response->created_at}}'>{{ $response->created_at->format('d M Y') }}</td>
                      </tr>
                      @endforeach
                    </tbody>
		    	</table>
			</div>
		</div>
	    <script type="text/php">
	    	@if (strtolower($orientation) == 'portrait')
	        if ( isset($pdf) ) {
	            $pdf->page_text(30, ($pdf->get_height() - 26.89), "Date Printed: " . date('d M Y H:i:s'), null, 10);
	        	$pdf->page_text(($pdf->get_width() - 84), ($pdf->get_height() - 26.89), "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10);
	        }
		    @elseif (strtolower($orientation) == 'landscape')
		    if ( isset($pdf) ) {
		        $pdf->page_text(30, ($pdf->get_height() - 26.89), "Date Printed: " . date('d M Y H:i:s'), null, 10);
		    	$pdf->page_text(($pdf->get_width() - 84), ($pdf->get_height() - 26.89), "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10);
		    }
		    @endif
	    </script>
	</body>
</html>
