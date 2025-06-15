@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];
$gene_array = $info['gene_array'];
$cnvr_array = $info['cnvr_array'];
$binding_TF_array = $info['binding_TF_array'];
$chromosome_array = $info['chromosome_array'];

@endphp


@extends('system.header_new')


@section('content')

<div class="page-content">
    <section>
        <div class="pbmit-heading">
            <h2 class="pbmit-title">Genomic Variations Explorer<br>
            <br>
                <strong>Promoter Search</strong>
            </h2>
        </div><br><br><br><br>
        <!-- <div class="appoinment-four-bg"> -->
        <div>
            <div class="container whiteText">
                <div class="row">
                    <div class="col-md-6 p-0">
                    <div class="appoinment-four-box">
                            <h3>Search by Gene IDs</h3>
                            <form class="form-style-2" action="{{ route('system.tools.GenVarX.viewPromotersByGenes', ['organism'=>$organism]) }}" method="get" target="_blank" >
                                <div class="row">
                                <div class="col-md-12"> 
                                <label for="gene1" ><b>Gene IDs:</b></label><br />
                                    <span style="font-size:10pt">
                                        &nbsp;(eg
                                        @foreach($gene_array as $gene)
                                        {{ $gene->Gene }}
                                        @endforeach
                                        )
                                    </span><br />
                                   
                                    <textarea id="gene1" name="gene1" rows="11" cols="40" class="form-control"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                    <label for="upstream_length_1"><b>Upstream length (bp):</b></label>
				                        <span style="font-size:10pt">(eg 2000)</span>
                                        <input type="text" id="upstream_length_1" name="upstream_length_1" size="40" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" value="Search" class="pbmit-btn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="appoinment-four-box">
                            <h3>Search by Binding TFs</h3>
                            <form class="form-style-2" action="{{ route('system.tools.GenVarX.viewPromotersByBindingTFs', ['organism'=>$organism]) }}" method="get" target="_blank" >
                                <div class="row"> 
                                <div class="col-md-12">
                                    <label for="bindingTF1"><b>Binding TFs:</b></label><br />
                                        <span style="font-size:10pt">
                                            &nbsp;(eg
                                            @foreach($binding_TF_array as $binding_TF)
                                            {{ $binding_TF->Motif }}
                                            @endforeach
                                            )
                                        </span><br />
                                        <textarea id="bindingTF1" name="bindingTF1" rows="7" class="form-control" cols="40"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                    <label for="chromosome1"><b>Gene Binding Chromosome:</b></label>
                                        <select name="chromosome1" id="chromosome1" class="form-control">
                                        @php
                                        for ($i = 0; $i < count($chromosome_array); $i++) {
                                            echo "<option value=\"" . $chromosome_array[$i]->Chromosome . "\">" . $chromosome_array[$i]->Chromosome . "</option>";
                                        }
                                        @endphp
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                    <label for="upstream_length_1"><b>Upstream length (bp):</b></label>
                                        <span style="font-size:10pt">(eg 2000)</span>
                                        <input type="text" id="upstream_length_1" class="form-control" name="upstream_length_1" size="40">
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <input type="submit" value="SEARCH" class="pbmit-btn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <br><br>
    <section>
        <div class="pbmit-heading">
            <h2 class="pbmit-title">
                <strong>Copy Number Variation Search</strong>
            </h2>
        </div>
        <!-- <div class="appoinment-section-four">
            <div class="container">
                <div class="row">
                    <div class="col-md-9">
                        <div class="pbmit-heading">
                            <h2 class="pbmit-title">
                                <strong>Copy Number Variation Search</strong>
                            </h2>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div> -->
        <br><br><br><br>	
        <div>
            <div class="container whiteText">
                <div class="row">
                    <div class="col-md-6 p-0">
                        <div class="appoinment-four-box">
                            <h3>Search by Gene IDs</h3>
                            <form class="form-style-2" action="{{ route('system.tools.GenVarX.viewAllCNVByGenes', ['organism'=>$organism]) }}" method="get" target="_blank">
                                <div class="row"> 
                                    <div class="col-md-12">
                                    <label for="gene_id_2"><b>Gene IDs:</b></label>
                                    <span style="font-size:10pt">
                                        &nbsp;(eg
                                        @foreach($gene_array as $gene)
                                        {{ $gene->Gene }}
                                        @endforeach
                                        )
                                    </span>
                                    <textarea id="gene_id_2" name="gene_id_2" rows="15" class="form-control" cols="40"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                    <label for="cnv_data_option_2"><b>Data Option:</b></label>
                                        <select name="cnv_data_option_2" id="cnv_data_option_2" class="form-control" >
                                            <option value="Individual_Hits" class="form-control">Individual Hits</option>
                                            <option value="Consensus_Regions" selected class="form-control">Consensus Regions</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <input type="submit" value="SEARCH" class="pbmit-btn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="appoinment-four-box">
                            <h3>Search By Accession and Copy Numbers</h3>
                            <form class="form-style-2" action="{{ route('system.tools.GenVarX.viewAllCNVByAccessionAndCopyNumbers', ['organism'=>$organism]) }}" method="get" target="_blank" >
                                <div class="row"> 
                                    <div class="col-md-12">
                                    <label for="accession_2"><b>Accession:</b></label>
                                        <span style="font-size:10pt">
                                            &nbsp;(eg
                                            @foreach($cnvr_array as $cnvr)
                                            {{ $cnvr->Accession }}
                                            @endforeach
                                            )
                                        </span>
                                        <input type="text" id="accession_2" class="form-control" name="accession_2" size="50">
                                    </div>
                                    <div class="col-md-12">
                                    <label for="copy_number_2"><b>Copy Numbers:</b></label><br />
                                        <span style="font-size:10pt">
                                            (eg CN0 CN1 CN2 CN3 CN4 CN5 CN6 CN7 CN8)
                                        </span>
                                        <textarea id="copy_number_2" name="copy_number_2" rows="10" cols="50" class="form-control"></textarea>
                                        
                                    </div>
                                    <div class="col-md-12">
                                    <label for="cnv_data_option_2"><b>Data Option:</b></label>
                                        <select name="cnv_data_option_2" id="cnv_data_option_2" class="form-control">
                                            <option value="Individual_Hits">Individual Hits</option>
                                            <option value="Consensus_Regions" selected>Consensus Regions</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" value="SEARCH" class="pbmit-btn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <br><br><br><br><br><br>
                <div class="row">  
                    <div class="col-md-6 p-0">
                        <div class="appoinment-four-box">
                            <h3>Search By Chromosome and Region</h3>
                            <form class="form-style-2" action="{{ route('system.tools.GenVarX.viewAllCNVByChromosomeAndRegion', ['organism'=>$organism]) }}" method="get" target="_blank" >
                                <div class="row"> 
                                    <div class="col-md-12">
                                    <label for="chromosome_2"><b>Chromosome:</b></label>
                                        <span style="font-size:10pt">
                                            &nbsp;(eg
                                            @foreach($cnvr_array as $cnvr)
                                            {{ $cnvr->Chromosome }}
                                            @endforeach
                                            )
                                        </span>
                                        <input type="text" id="chromosome_2" class="form-control" name="chromosome_2" size="40">
                                    </div>
                                    <div class="col-md-12">
                                    <label for="position_start_2"><b>Starting Position:</b></label>
                                    <span style="font-size:10pt">
                                        &nbsp;(eg
                                        @foreach($cnvr_array as $cnvr)
                                        {{ $cnvr->Start }}
                                        @endforeach
                                        )
                                    </span>
                                    <input type="text" id="position_start_2" class="form-control" name="position_start_2" size="40">
                                    </div>
                                    <div class="col-md-12">
                                    <label for="position_end_2"><b>Ending Position:</b></label>
                                    <span style="font-size:10pt">
                                        &nbsp;(eg
                                        @foreach($cnvr_array as $cnvr)
                                        {{ $cnvr->End }}
                                        @endforeach
                                        )
                                    </span>
                                    <input type="text" id="position_end_2" class="form-control" name="position_end_2" size="40">
                                    </div>
                                    <div class="col-md-12">
                                    <label for="cnv_data_option_2"><b>Data Option:</b></label>
                                    <select name="cnv_data_option_2" id="cnv_data_option_2" class="form-control">
                                        <option value="Individual_Hits">Individual Hits</option>
                                        <option value="Consensus_Regions" selected>Consensus Regions</option>
                                    </select>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" value="SEARCH" class="pbmit-btn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <br><br>
    
        <input type="submit" onclick="downloadUserManual()" value="Download User Manual" class="pbmit-btn">
    
    <br><br>
    <section>
        <div class="container g-0">
            <div class="counter-section-six pbmit-bgcolor-skincolor">	
                <div class="row">
                <div class="col-md-12">
                        <div class="pbmit-fidbox-style-3">
                            <div class="pbmit-fld-contents">
                                <h4 class="pbmit-fid-title"><span>If you use the Genomic Variations Explorer in your work, please cite:<br></span></h4>
                                <h5 class="pbmit-fid-title"><span><p> Chan YO, Biova J, Mahmood A, Dietz N, Bilyeu K, Škrabišová M, Joshi T: <b> Genomic Variations Explorer (GenVarX): A Toolset for Annotating Promoter and CNV Regions Using Genotypic and Phenotypic Differences. </b> Frontiers in Genetics 2023, In Press. </p></span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
			

</div>

@endsection


@section('javascript')

<script type="text/javascript">
	let gene_array = <?php echo json_encode($gene_array); ?>;
	let binding_TF_array = <?php echo json_encode($binding_TF_array); ?>;

	// Populate gene1 textarea placeholder
	gene1_placeholder = "\nPlease separate each gene into a new line.\n\nExample:\n";
	for (let i = 0; i < gene_array.length; i++) {
		gene1_placeholder += gene_array[i]['Gene'] + "\n";
	}
	document.getElementById('gene1').placeholder = gene1_placeholder;

	// Populate bindingTF1 textarea placeholder
	bindingTF1_placeholder = "\nPlease separate each gene into a new line.\n\nExample:\n";
	for (let i = 0; i < binding_TF_array.length; i++) {
		bindingTF1_placeholder += binding_TF_array[i]['Motif'] + "\n";
	}
	document.getElementById('bindingTF1').placeholder = bindingTF1_placeholder;

	// Populate gene2 textarea placeholder
	gene2_placeholder = "\nPlease separate each gene into a new line.\n\nExample:\n";
	for (let i = 0; i < gene_array.length; i++) {
		gene2_placeholder += gene_array[i]['Gene'] + "\n";
	}
	document.getElementById('gene_id_2').placeholder = gene2_placeholder;

	// Populate copy_number_2 textarea placeholder
	copy_number2_placeholder = "\nPlease separate each copy number into a new line.\n\nExample:\nCN0\nCN1\nCN3\n\n * CN2 represents normal.\n** CN2 is not in individual hits dataset.\n";
	document.getElementById('copy_number_2').placeholder = copy_number2_placeholder;

	function downloadUserManual() {
		let downloadAnchorNode = document.createElement('a');
		downloadAnchorNode.setAttribute("href", "{{ asset('system/home/GenVarX/assets/User_Manual/GenVarX_User_Manual.pdf') }}");
		downloadAnchorNode.setAttribute("target", "_blank");
		document.body.appendChild(downloadAnchorNode); // required for firefox
		downloadAnchorNode.click();
		downloadAnchorNode.remove();
	}
</script>

@endsection