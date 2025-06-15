@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $result_arr = $info['result_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Back button -->
    <a href="{{ route('system.tools.GenVarX', ['organism' => $organism]) }}"><button> &lt; Back </button></a>

    <br />
    <br />

    @php

        for ($i = 0; $i < count($result_arr); $i++) {
            echo '<b>Queried Gene: </b>' .
                $result_arr[$i]->Name .
                ' (' .
                $result_arr[$i]->Chromosome .
                ':' .
                $result_arr[$i]->Start .
                '-' .
                $result_arr[$i]->End .
                ') (' .
                $result_arr[$i]->Strand .
                ')';
            echo '<br /><br />';
            echo '<b>Promoter Region: </b>' . $result_arr[$i]->Promoter_Start . '-' . $result_arr[$i]->Promoter_End;
            echo '<br /><br />';

            if (count($result_arr[$i]->Motif_Data) > 0) {
                echo "<div style=\"width:auto; height:auto; border:3px solid #000; max-height:1000px; overflow:scroll;\">";
                echo "<table style=\"text-align:center; width:100%;\">";

                // Table header
                echo '<tr>';
                foreach ($result_arr[$i]->Motif_Data[0] as $key => $value) {
                    echo "<th style=\"border:1px solid black; text-align:center; min-width:80px;\">" . $key . '</th>';
                }
                echo '</tr>';

                // Table row
                for ($j = 0; $j < count($result_arr[$i]->Motif_Data); $j++) {
                    // Table row
                    echo "<tr bgcolor=\"" . ($j % 2 ? '#FFFFFF' : '#DDFDD') . "\">";
                    foreach ($result_arr[$i]->Motif_Data[$j] as $key => $value) {
                        if ($key == 'Binding_TF') {
                            echo "<td style=\"border:1px solid black; min-width:80px;\">";
                            echo "<a href=\"javascript:void(0);\" onclick=\"getMotifWeblogo('" .
                                $organism .
                                "', '" .
                                $value .
                                "', '" .
                                $result_arr[$i]->Name .
                                "', '" .
                                $result_arr[$i]->Motif_Data[$j]->Chromosome .
                                "', '" .
                                $result_arr[$i]->Motif_Data[$j]->Start .
                                "', '" .
                                $result_arr[$i]->Motif_Data[$j]->End .
                                "', '" .
                                $result_arr[$i]->Motif_Data[$j]->Gene_Binding_Sequence .
                                "')\">";
                            echo $value;
                            echo '</a>';
                            echo '</td>';
                        } else {
                            echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . '</td>';
                        }
                    }
                    echo '</tr>';
                }

                echo '</table>';
                echo '</div>';

                echo '<br />';

                // Div tags for selected motif, weblogo, and motif sequence table
                echo "<div id=\"" .
                    $result_arr[$i]->Name .
                    "_b\" style='width:auto; height:auto; overflow:visible; max-height:1000px;'></div>";
                echo "<div id=\"" .
                    $result_arr[$i]->Name .
                    "_weblogo\" style='width:auto; height:auto; overflow:visible; max-height:1000px;'></div>";
                echo "<div id=\"" .
                    $result_arr[$i]->Name .
                    "_detail_table\" style='width:auto; height:auto; overflow:visible; max-height:1000px;'></div>";

                echo '<br /><br />';
            } else {
                echo '<p>No binding TF found in our database!!!</p><br /><br />';
            }
        }

    @endphp
</body>


<script type="text/javascript">
    function getMotifWeblogo(organism, motif, gene, chromosome, motif_start, motif_end, motif_sequence) {

        // Clear data appended to the div tags, if there is any
        if (document.getElementById(gene + "_b").innerHTML) {
            document.getElementById(gene + "_b").innerHTML = null;
        }
        if (document.getElementById(gene + "_weblogo").innerHTML) {
            document.getElementById(gene + "_weblogo").innerHTML = null;
        }
        if (document.getElementById(gene + "_detail_table").innerHTML) {
            document.getElementById(gene + "_detail_table").innerHTML = null;
        }

        // Create b tag for motif
        var motif_b = document.createElement("b");
        motif_b.innerHTML = "Selected TF: " + motif;
        document.getElementById(gene + "_b").appendChild(motif_b);

        // Load Ceqlogo / Weblogo image
        var weblogo = document.createElement("img");

        if (organism === "Osativa") {
            weblogo.setAttribute("src", "{{ asset('system/home/GenVarX/assets/mViz_Rice_Japonica_motif_weblogos') }}" +
                "/" + motif + ".png");
            document.getElementById(gene + "_weblogo").appendChild(weblogo);
        } else if (organism === "Athaliana") {
            weblogo.setAttribute("src", "{{ asset('system/home/GenVarX/assets/mViz_Arabidopsis_motif_weblogos') }}" +
                "/" + motif + ".png");
            document.getElementById(gene + "_weblogo").appendChild(weblogo);
        } else if (organism === "Zmays") {
            weblogo.setAttribute("src", "{{ asset('system/home/GenVarX/assets/mViz_Maize_motif_weblogos') }}" + "/" +
                motif + ".png");
            document.getElementById(gene + "_weblogo").appendChild(weblogo);
        }

        // Create motif sequence table
        let detail_table = document.createElement("table");
        detail_table.setAttribute("style", "text-align:center; border:3px solid #000;");
        let detail_tr_index = document.createElement("tr");
        let detail_tr_position = document.createElement("tr");
        let detail_tr_nucleotide = document.createElement("tr");
        let detail_tr_genotype_count = document.createElement("tr");

        for (let i = 0; i < (motif_end - motif_start + 1); i++) {
            var detail_th = document.createElement("th");
            detail_th.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            detail_th.innerHTML = Number(i) + 1;
            detail_tr_index.appendChild(detail_th);

            var detail_th = document.createElement("th");
            detail_th.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            detail_th.innerHTML = Number(motif_start) + Number(i);
            detail_tr_position.appendChild(detail_th);

            var detail_td = document.createElement("td");
            detail_td.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            detail_td.innerHTML = motif_sequence[i];
            detail_tr_nucleotide.appendChild(detail_td);

            var detail_td = document.createElement("td");
            detail_td.id = "genotype_count_" + (Number(motif_start) + Number(i)).toString();
            detail_tr_genotype_count.appendChild(detail_td);
        }

        detail_table.appendChild(detail_tr_index);
        detail_table.appendChild(detail_tr_position);
        detail_table.appendChild(detail_tr_nucleotide);
        detail_table.appendChild(detail_tr_genotype_count);

        document.getElementById(gene + "_detail_table").appendChild(detail_table);


        $.ajax({
            url: 'queryGenotypeCount/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Chromosome: chromosome,
                Start: motif_start,
                End: motif_end
            },
            success: function(response) {
                let res = JSON.parse(response);

                for (let i = 0; i < res.length; i++) {
                    var genotype_count_element = document.getElementById("genotype_count_" + (res[i][
                        'Position'
                    ]).toString());

                    if (genotype_count_element.innerHTML === null || genotype_count_element.innerHTML ==
                        '') {
                        // Construct table with header
                        genotype_count_element.setAttribute("style",
                            "border:1px solid black; min-width:80px; height:18.5px;");
                        var genotype_count_table = document.createElement("table");
                        genotype_count_table.id = "genotype_count_" + (res[i]['Position']).toString() +
                            "_table";
                        var genotype_count_tr_index = document.createElement("tr");
                        var detail_th = document.createElement("th");
                        detail_th.setAttribute("style",
                            "border:1px solid black; min-width:20px; height:18.5px;");
                        detail_th.innerHTML = "Genotype";
                        genotype_count_tr_index.appendChild(detail_th);
                        var detail_th = document.createElement("th");
                        detail_th.setAttribute("style",
                            "border:1px solid black; min-width:20px; height:18.5px;");
                        detail_th.innerHTML = "Category";
                        genotype_count_tr_index.appendChild(detail_th);
                        var detail_th = document.createElement("th");
                        detail_th.setAttribute("style",
                            "border:1px solid black; min-width:20px; height:18.5px;");
                        detail_th.innerHTML = "Count";
                        genotype_count_tr_index.appendChild(detail_th);
                        genotype_count_table.appendChild(genotype_count_tr_index);
                        genotype_count_element.appendChild(genotype_count_table);
                    }
                }

                for (let i = 0; i < res.length; i++) {
                    var genotype_count_element_table = document.getElementById("genotype_count_" + (res[i][
                        'Position'
                    ]).toString() + "_table");

                    var genotype_count_tr_index = document.createElement("tr");
                    var detail_td = document.createElement("td");
                    detail_td.setAttribute("style",
                        "border:1px solid black; min-width:20px; height:18.5px;");
                    detail_td.innerHTML = res[i]['Genotype'];
                    genotype_count_tr_index.appendChild(detail_td);
                    var detail_td = document.createElement("td");
                    detail_td.setAttribute("style",
                        "border:1px solid black; min-width:20px; height:18.5px;");
                    detail_td.innerHTML = res[i]['Category'];
                    genotype_count_tr_index.appendChild(detail_td);
                    var detail_td = document.createElement("td");
                    detail_td.setAttribute("style",
                        "border:1px solid black; min-width:20px; height:18.5px;");
                    var position_a = document.createElement("a");
                    position_a.href = "../viewVariantAndPhenotype/" + organism + "?Chromosome=" + res[i][
                        'Chromosome'
                    ] + "&Position=" + res[i]['Position'] + "&Genotype=" + res[i]['Genotype'];
                    position_a.target = "_blank";
                    position_a.innerHTML = res[i]['Count']
                    detail_td.appendChild(position_a);
                    genotype_count_tr_index.appendChild(detail_td);

                    genotype_count_element_table.appendChild(genotype_count_tr_index);

                }

            },
            error: function(xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
            }
        });


        // Change the overflow style of the div to scroll
        document.getElementById(gene + "_b").style.overflow = 'scroll';
        document.getElementById(gene + "_weblogo").style.overflow = 'scroll';
        document.getElementById(gene + "_detail_table").style.overflow = 'scroll';
    }
</script>
