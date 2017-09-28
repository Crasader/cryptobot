@extends('layouts.app')

@section('content')

<section id="dashboard">

	<div class="grid-container fluid">

	    <div class="grid-x grid-padding-x">

	        <div class="form-container section-title cell text-left">
	            <h1>Dashboard</h1>
	        </div>

	        <div class="form-container cell text-center portfolio">
	
				<balance type="header" fiat="{{ $fiat }}"></balance>

                @foreach ($coins as $coin)
            	<balance 
                	type="item" 
                	coin="{{ $coin['Name'] }}" 
                	logo="{{ $coin['LogoUrl'] }}" 
                	balance="{{ $coin['Balance'] }}"  
                	price="{{ $coin['Price'] }}" 
                	valueBTC="{{ $coin['BTC-Value'] }}" 
                	valueEUR="{{ $coin['EUR-Value'] }}" 
                	valueUSD="{{ $coin['USD-Value'] }}" 
                	gain=""
             		fiat="{{ $fiat }}">
            	</balance>
				@endforeach

	        </div>

	   	</div>

	</div>

</section>

@endsection
