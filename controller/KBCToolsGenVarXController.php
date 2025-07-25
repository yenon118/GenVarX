<?php

namespace App\Http\Controllers\System\Tools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;


class KBCToolsGenVarXController extends Controller
{


    function __construct() {}


    function clean_malicious_input($in_var)
    {
        $string_max_allow_length = 2000;

        if (isset($in_var)) {
            if (!empty($in_var)) {
                // Handle if the input is a string
                if (is_string($in_var)) {
                    // Truncate string if longer than the max allowed length
                    if (strlen($in_var) > $string_max_allow_length) {
                        $in_var = substr($in_var, 0, $string_max_allow_length);
                    }
                    // Remove potentially dangerous characters and strip tags
                    $in_var = preg_replace('/[\[\]\{\}\(\)\\\=\/\'\"\:]/', '', $in_var);
                    $in_var = strip_tags($in_var);
                    // Encode special characters to prevent XSS
                    $in_var = htmlspecialchars($in_var, ENT_QUOTES, 'UTF-8');
                    // Trim the result
                    return trim($in_var);
                }
                // Handle if the input is an array
                elseif (is_array($in_var)) {
                    $out_var = [];
                    foreach ($in_var as $key => $value) {
                        // Recursively clean each element of the array
                        $out_var[$key] = self::clean_malicious_input($value);
                    }
                    return $out_var;
                }
                // Handle if the input is numeric or boolean
                elseif (is_numeric($in_var) || is_bool($in_var)) {
                    return $in_var;
                }
            }
        }
        return null;
    }


    public function GenVarXPage(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        // Table names
        if ($organism == "Osativa") {
            $table_name = "mViz_Rice_Japonica_Motif";
            $cnvr_table_name = "mViz_Rice_Nipponbare_CNVR";
            $gff_table_name = "mViz_Rice_Nipponbare_GFF";
        } elseif ($organism == "Athaliana") {
            $table_name = "mViz_Arabidopsis_Motif";
            $cnvr_table_name = "mViz_Arabidopsis_CNVR";
            $gff_table_name = "mViz_Arabidopsis_GFF";
        } elseif ($organism == "Zmays") {
            $table_name = "mViz_Maize_Motif";
            $cnvr_table_name = "mViz_Maize_CNVR";
            $gff_table_name = "mViz_Maize_GFF";
        }

        if (isset($table_name) && isset($cnvr_table_name) && isset($gff_table_name)) {
            // Query gene from database
            $sql = "SELECT DISTINCT M.Gene ";
            $sql = $sql . "FROM " . $db . "." . $gff_table_name . " AS GFF ";
            $sql = $sql . "INNER JOIN " . $db . "." . $table_name . " AS M ";
            $sql = $sql . "ON M.Gene = GFF.Name ";
            $sql = $sql . "INNER JOIN " . $db . "." . $cnvr_table_name . " AS CNVR ";
            $sql = $sql . "ON CNVR.Chromosome = GFF.Chromosome AND CNVR.Start < GFF.Start AND CNVR.End > GFF.End ";
            if ($organism == "Zmays") {
                $sql = $sql . "WHERE M.Gene LIKE 'GRMZ%' ";
            }
            $sql = $sql . "LIMIT 3;";

            $gene_array = DB::connection($db)->select($sql);

            // Query binding TF from database
            $sql = "SELECT DISTINCT Motif ";
            $sql = $sql . "FROM " . $db . "." . $table_name . " ";
            if ($organism == "Zmays") {
                $sql = $sql . "WHERE Motif LIKE 'GRMZ%' ";
            }
            $sql = $sql . "LIMIT 3;";

            $binding_tf_array = DB::connection($db)->select($sql);

            // Query binding TF from database
            $sql = "SELECT DISTINCT Chromosome FROM " . $db . "." . $gff_table_name . " ORDER BY Chromosome; ";

            $chromosome_array = DB::connection($db)->select($sql);
        }

        // Get one CNVR result
        if ($organism == "Osativa" || $organism == "Athaliana" || $organism == "Zmays") {
            // Query chromosme, region, and accession from database
            $sql = "SELECT * FROM " . $db . "." . $cnvr_table_name . " LIMIT 1;";
            $cnvr_array = DB::connection($db)->select($sql);
        }

        if (isset($table_name) && isset($gene_array) && isset($cnvr_table_name) && isset($cnvr_array)) {
            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'gene_array' => $gene_array,
                'binding_TF_array' => $binding_tf_array,
                'cnvr_array' => $cnvr_array,
                'chromosome_array' => $chromosome_array
            ];

            // Return to view
            return view('system/tools/GenVarX/GenVarXNew')->with('info', $info);
        } else {
            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
            ];

            // Return to view
            return view('system/tools/GenVarX/GenVarXNotAvailable')->with('info', $info);
        }
    }


    public function ViewPromotersByGenesPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $gene1 = $request->gene1;
            $upstream_length_1 = $request->upstream_length_1;

            $gene1 = self::clean_malicious_input($gene1);

            $upstream_length_1 = self::clean_malicious_input($upstream_length_1);
            $upstream_length_1 = preg_replace('/\s+/', '', $upstream_length_1);

            // Convert gene1 string to array
            $gene_arr = array();
            if (is_string($gene1)) {
                $gene1 = trim($gene1);
                $temp_gene_arr = preg_split("/[;, \n]+/", $gene1);
                $gene_arr = array();
                for ($i = 0; $i < count($temp_gene_arr); $i++) {
                    if (!empty(preg_replace('/\s+/', '', $temp_gene_arr[$i]))) {
                        array_push($gene_arr, preg_replace('/\s+/', '', $temp_gene_arr[$i]));
                    }
                }
            } elseif (is_array($gene1)) {
                $temp_gene_arr = $gene1;
                $gene_arr = array();
                for ($i = 0; $i < count($temp_gene_arr); $i++) {
                    if (!empty(preg_replace('/\s+/', '', $temp_gene_arr[$i]))) {
                        array_push($gene_arr, preg_replace('/\s+/', '', $temp_gene_arr[$i]));
                    }
                }
            }

            // Convert upstream length to integer
            if (is_string($upstream_length_1)) {
                $upstream_length_1 = preg_replace("/[^0-9.]/", "", $upstream_length_1);
                $upstream_length = intval(floatval(trim($upstream_length_1)));
            } elseif (is_int($upstream_length_1)) {
                $upstream_length = $upstream_length_1;
            } elseif (is_float($upstream_length_1)) {
                $upstream_length = intval($upstream_length_1);
            } else {
                $upstream_length = 2000;
            }

            $upstream_length = abs($upstream_length);

            if ($upstream_length > 6000) {
                $upstream_length = 6000;
            }

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                $motif_table_name = "mViz_Rice_Japonica_Motif";
                $motif_sequence_table_name = "mViz_Rice_Japonica_";
                $tf_table_name = "mViz_Rice_Japonica_TF";
                $genotype_data_table_name = "mViz_Rice_";
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                $motif_table_name = "mViz_Arabidopsis_Motif";
                $motif_sequence_table_name = "mViz_Arabidopsis_";
                $tf_table_name = "mViz_Arabidopsis_TF";
                $genotype_data_table_name = "mViz_Arabidopsis_";
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                $motif_table_name = "mViz_Maize_Motif";
                $motif_sequence_table_name = "mViz_Maize_";
                $tf_table_name = "mViz_Maize_TF";
                $genotype_data_table_name = "mViz_Maize_";
            }

            $query_str = "SELECT * FROM " . $db . "." . $table_name . " WHERE (Name IN ('";
            for ($i = 0; $i < count($gene_arr); $i++) {
                if ($i < (count($gene_arr) - 1)) {
                    $query_str = $query_str . $gene_arr[$i] . "', '";
                } else {
                    $query_str = $query_str . $gene_arr[$i];
                }
            }
            $query_str = $query_str . "'));";
            $result_arr = DB::connection($db)->select($query_str);

            // Calculate promoter start and end
            for ($i = 0; $i < count($result_arr); $i++) {
                if ($result_arr[$i]->Strand == '+') {
                    $result_arr[$i]->Promoter_End = $result_arr[$i]->Start - 1;
                    $result_arr[$i]->Promoter_Start = ((($result_arr[$i]->Promoter_End - $upstream_length) > 0) ? ($result_arr[$i]->Promoter_End - $upstream_length) : 1);
                } elseif ($result_arr[$i]->Strand == '-') {
                    $result_arr[$i]->Promoter_Start = $result_arr[$i]->End + 1;
                    $result_arr[$i]->Promoter_End = $result_arr[$i]->Promoter_Start + $upstream_length;
                }
            }

            // Get motifs
            for ($i = 0; $i < count($result_arr); $i++) {

                // Get binding TF
                // $query_str = "
                // SELECT M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, MS.Name AS Binding_TF, TF.TF_Family,
                // MS.Sequence AS Gene_Binding_Sequence, GROUP_CONCAT(GD.Position ORDER BY GD.Position ASC SEPARATOR ', ') AS Variant_Positions FROM (
                //     SELECT Motif, Gene FROM " . $db . "." . $motif_table_name . " WHERE Gene = '" . $result_arr[$i]->Name . "'
                // ) AS M
                // INNER JOIN (
                //     SELECT Chromosome, Start, End, Strand, Name, Sequence FROM " . $db . "." . $motif_sequence_table_name . $result_arr[$i]->Chromosome. "_Motif_Sequence
                //     WHERE (Chromosome = '" . $result_arr[$i]->Chromosome . "')
                //     AND ((Start BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . " ) OR (End BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . "))
                // ) AS MS
                // ON M.Motif = MS.Name
                // LEFT JOIN " . $db . "." . $tf_table_name . " AS TF ON MS.Name = TF.TF
                // LEFT JOIN (
                //     SELECT DISTINCT Position FROM " . $db . "." . $genotype_data_table_name . $result_arr[$i]->Chromosome . "_genotype_data
                //     WHERE (Position BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . ")
                // ) AS GD
                // ON (GD.Position BETWEEN MS.Start AND MS.End)
                // GROUP BY M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, Binding_TF, TF.TF_Family, Gene_Binding_Sequence
                // ORDER BY Start, End;
                // ";

                // Get binding TF (Optimized MySQL query string)
                $query_str = "
                SELECT M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, MS.Name AS Binding_TF, TF.TF_Family,
                MS.Sequence AS Gene_Binding_Sequence, GROUP_CONCAT(DISTINCT GD.Position ORDER BY GD.Position ASC SEPARATOR ', ') AS Variant_Positions
                FROM " . $db . "." . $motif_table_name . " AS M
                INNER JOIN " . $db . "." . $motif_sequence_table_name . $result_arr[$i]->Chromosome . "_Motif_Sequence AS MS
                ON M.Motif = MS.Name
                LEFT JOIN " . $db . "." . $tf_table_name . " AS TF ON MS.Name = TF.TF
                LEFT JOIN (
                    SELECT DISTINCT Position FROM " . $db . "." . $genotype_data_table_name . $result_arr[$i]->Chromosome . "_genotype_data
                    WHERE (Position BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . ")
                ) AS GD
                ON (GD.Position BETWEEN MS.Start AND MS.End)
                WHERE (M.Gene = '" . $result_arr[$i]->Name . "') AND (MS.Chromosome = '" . $result_arr[$i]->Chromosome . "') AND ((MS.Start BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . " ) OR (MS.End BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . "))
                GROUP BY M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, Binding_TF, TF.TF_Family, Gene_Binding_Sequence
                ORDER BY Start, End;
                ";

                // Get binding TF
                // $query_str2 = "
                // SELECT M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, MS.Name AS Binding_TF, TF.TF_Family, MS.Sequence AS Gene_Binding_Sequence FROM (
                //     SELECT Motif, Gene FROM " . $db . "." . $motif_table_name . " WHERE Gene = '" . $result_arr[$i]->Name . "'
                // ) AS M
                // INNER JOIN (
                //     SELECT Chromosome, Start, End, Strand, Name, Sequence FROM " . $db . "." . $motif_sequence_table_name . $result_arr[$i]->Chromosome. "_Motif_Sequence
                //     WHERE (Chromosome = '" . $result_arr[$i]->Chromosome . "')
                //     AND ((Start BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . " ) OR (End BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . "))
                // ) AS MS
                // ON M.Motif = MS.Name
                // LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
                // ON MS.Name = TF.TF
                // ORDER BY Start, End;
                // ";

                // Get binding TF (Optimized MySQL query string)
                $query_str2 = "
                SELECT M.Gene, MS.Chromosome, MS.Start, MS.End, MS.Strand, MS.Name AS Binding_TF, TF.TF_Family, MS.Sequence AS Gene_Binding_Sequence
                FROM " . $db . "." . $motif_table_name . " AS M
                INNER JOIN " . $db . "." . $motif_sequence_table_name . $result_arr[$i]->Chromosome . "_Motif_Sequence AS MS
                ON M.Motif = MS.Name
                LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
                ON MS.Name = TF.TF
                WHERE (M.Gene = '" . $result_arr[$i]->Name . "') AND (MS.Chromosome = '" . $result_arr[$i]->Chromosome . "')
                AND ((MS.Start BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . " ) OR (MS.End BETWEEN " . $result_arr[$i]->Promoter_Start . " AND " . $result_arr[$i]->Promoter_End . "))
                ORDER BY Start, End;
                ";

                try {
                    $motif_result_arr = DB::connection($db)->select($query_str);
                } catch (\Throwable $e) {
                    $motif_result_arr = DB::connection($db)->select($query_str2);
                }

                $result_arr[$i]->Motif_Data = $motif_result_arr;
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'result_arr' => $result_arr,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewPromotersByGenes')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function QueryGenotypeCount(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        $chromosome = $request->Chromosome;
        $position_start = $request->Start;
        $position_end = $request->End;

        $chromosome = self::clean_malicious_input($chromosome);

        $position_start = self::clean_malicious_input($position_start);

        $position_end = self::clean_malicious_input($position_end);

        // Trim string
        if (is_string($chromosome)) {
            $chromosome = preg_replace('/\s+/', '', $chromosome);
        }
        if (is_string($position_start)) {
            $position_start = preg_replace("/[^0-9.]/", "", $position_start);
        }
        if (is_string($position_end)) {
            $position_end = preg_replace("/[^0-9.]/", "", $position_end);
        }

        // Table names
        if ($organism == "Osativa") {
            $table_name = "mViz_Rice_" . $chromosome . "_genotype_count";
        } elseif ($organism == "Athaliana") {
            $table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_count";
        } elseif ($organism == "Zmays") {
            $table_name = "mViz_Maize_" . $chromosome . "_genotype_count";
        }

        // Query string
        $query_str = "SELECT * ";
        $query_str = $query_str . "FROM " . $db . "." . $table_name . " ";
        $query_str = $query_str . "WHERE (Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "AND (Position BETWEEN " . $position_start . " AND " . $position_end . ") ";
        $query_str = $query_str . "ORDER BY Chromosome, Position, Count DESC;";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }


    public function ViewPromotersByBindingTFsPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $bindingTF1 = $request->bindingTF1;
            $chromosome1 = $request->chromosome1;
            $upstream_length_1 = $request->upstream_length_1;

            $bindingTF1 = self::clean_malicious_input($bindingTF1);

            $chromosome1 = self::clean_malicious_input($chromosome1);
            $chromosome1 = preg_replace('/\s+/', '', $chromosome1);

            $upstream_length_1 = self::clean_malicious_input($upstream_length_1);
            $upstream_length_1 = preg_replace('/\s+/', '', $upstream_length_1);

            // Convert bindingTF1 string to array
            $binding_tf_arr = array();
            if (is_string($bindingTF1)) {
                $bindingTF1 = trim($bindingTF1);
                $temp_binding_tf_arr = preg_split("/[;, \n]+/", $bindingTF1);
                $binding_tf_arr = array();
                for ($i = 0; $i < count($temp_binding_tf_arr); $i++) {
                    if (!empty(trim($temp_binding_tf_arr[$i]))) {
                        array_push($binding_tf_arr, trim($temp_binding_tf_arr[$i]));
                    }
                }
            } elseif (is_array($bindingTF1)) {
                $temp_binding_tf_arr = $bindingTF1;
                $binding_tf_arr = array();
                for ($i = 0; $i < count($temp_binding_tf_arr); $i++) {
                    if (!empty(trim($temp_binding_tf_arr[$i]))) {
                        array_push($binding_tf_arr, trim($temp_binding_tf_arr[$i]));
                    }
                }
            }

            // Convert upstream length to integer
            if (is_string($upstream_length_1)) {
                $upstream_length_1 = preg_replace("/[^0-9.]/", "", $upstream_length_1);
                $upstream_length = intval(floatval(trim($upstream_length_1)));
            } elseif (is_int($upstream_length_1)) {
                $upstream_length = $upstream_length_1;
            } elseif (is_float($upstream_length_1)) {
                $upstream_length = intval($upstream_length_1);
            } else {
                $upstream_length = 2000;
            }

            $upstream_length = abs($upstream_length);

            if ($upstream_length > 6000) {
                $upstream_length = 6000;
            }

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                $motif_table_name = "mViz_Rice_Japonica_Motif";
                $motif_sequence_table_name = "mViz_Rice_Japonica_" . $chromosome1 . "_Motif_Sequence";
                $tf_table_name = "mViz_Rice_Japonica_TF";
                $genotype_data_table_name = "mViz_Rice_" . $chromosome1 . "_genotype_data";
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                $motif_table_name = "mViz_Arabidopsis_Motif";
                $motif_sequence_table_name = "mViz_Arabidopsis_" . $chromosome1 . "_Motif_Sequence";
                $tf_table_name = "mViz_Arabidopsis_TF";
                $genotype_data_table_name = "mViz_Arabidopsis_" . $chromosome1 . "_genotype_data";
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                $motif_table_name = "mViz_Maize_Motif";
                $motif_sequence_table_name = "mViz_Maize_" . $chromosome1 . "_Motif_Sequence";
                $tf_table_name = "mViz_Maize_TF";
                $genotype_data_table_name = "mViz_Maize_" . $chromosome1 . "_genotype_data";
            }

            // Get binding TFs
            $result_arr = array();
            for ($i = 0; $i < count($binding_tf_arr); $i++) {

                // Get binding TF
                // $query_str = "
                // SELECT M.Motif AS Binding_TF, TF.TF_Family,
                // MS.Chromosome AS Binding_Chromosome, MS.Start AS Binding_Start, MS.End AS Binding_End, MS.Sequence AS Gene_Binding_Sequence,
                // M.Gene, GFF.Chromosome, GFF.Start AS Gene_Start, GFF.End AS Gene_End, GFF.Strand AS Gene_Strand, GFF.Gene_Description
                // FROM (
                //     SELECT Motif, Gene FROM " . $db . "." . $motif_table_name . "
                //     WHERE Motif = '" . $binding_tf_arr[$i] . "'
                // ) AS M
                // LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
                // ON M.Motif = TF.TF
                // LEFT JOIN (
                //     SELECT Chromosome, Start, End, ID, Name, Sequence FROM " . $db . "." . $motif_sequence_table_name . "
                //     WHERE Name = '" . $binding_tf_arr[$i] . "'
                // ) AS MS
                // ON M.Motif = MS.Name
                // LEFT JOIN (
                //     SELECT ID, Name, Chromosome, Start, End, Strand, Gene_Description,
                //     CASE Strand
                //         WHEN '+' THEN Start-1-" . $upstream_length . "
                //         ELSE End+1
                //     END AS Promoter_Start,
                //     CASE Strand
                //         WHEN '+' THEN Start-1
                //         ELSE End+1+" . $upstream_length . "
                //     END AS Promoter_End
                //     FROM " . $db . "." . $table_name . "
                //     WHERE Chromosome = '" . $chromosome1 . "'
                // ) AS GFF
                // ON ((M.Gene = GFF.ID) AND (MS.Chromosome = GFF.Chromosome) AND (MS.Start BETWEEN GFF.Promoter_Start AND GFF.Promoter_End))
                // WHERE ((GFF.Chromosome = '" . $chromosome1 . "') AND (MS.Chromosome = GFF.Chromosome) AND (MS.Start BETWEEN GFF.Promoter_Start AND GFF.Promoter_End))
                // ORDER BY MS.Chromosome, MS.Start, MS.End;
                // ";

                // Get binding TF (Optimized MySQL query string)
                $query_str = "
                SELECT M.Motif AS Binding_TF, TF.TF_Family,
                MS.Chromosome AS Binding_Chromosome, MS.Start AS Binding_Start, MS.End AS Binding_End, MS.Sequence AS Gene_Binding_Sequence,
                M.Gene, GFF.Chromosome, GFF.Start AS Gene_Start, GFF.End AS Gene_End, GFF.Strand AS Gene_Strand, GFF.Gene_Description
                FROM " . $db . "." . $motif_table_name . " AS M
                LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
                ON M.Motif = TF.TF
                LEFT JOIN " . $db . "." . $motif_sequence_table_name . " AS MS
                ON M.Motif = MS.Name
                LEFT JOIN (
                    SELECT ID, Name, Chromosome, Start, End, Strand, Gene_Description,
                    CASE Strand
                        WHEN '+' THEN Start-1-" . $upstream_length . "
                        ELSE End+1
                    END AS Promoter_Start,
                    CASE Strand
                        WHEN '+' THEN Start-1
                        ELSE End+1+" . $upstream_length . "
                    END AS Promoter_End
                    FROM " . $db . "." . $table_name . "
                    WHERE Chromosome = '" . $chromosome1 . "'
                ) AS GFF
                ON ((M.Gene = GFF.ID) AND (MS.Chromosome = GFF.Chromosome) AND (MS.Start BETWEEN GFF.Promoter_Start AND GFF.Promoter_End))
                WHERE (MS.Chromosome = '" . $chromosome1 . "') AND (GFF.Chromosome = '" . $chromosome1 . "') AND (M.Motif = '" . $binding_tf_arr[$i] . "') AND (MS.Name = '" . $binding_tf_arr[$i] . "')
                ORDER BY MS.Chromosome, MS.Start, MS.End;
                ";

                $binding_tf_result_arr = DB::connection($db)->select($query_str);

                $result_arr[$binding_tf_arr[$i]] = $binding_tf_result_arr;
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'upstream_length' => $upstream_length,
                'binding_tf_arr' => $binding_tf_arr,
                'result_arr' => $result_arr,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewPromotersByBindingTFs')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function ViewPromoterOnSelectedBindingTFPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $motif = $request->Motif;
            $gene = $request->Gene;
            $chromosome = $request->Chromosome;
            $motif_start = $request->Motif_Start;
            $motif_end = $request->Motif_End;
            $gene_binding_sequence = $request->Gene_Binding_Sequence;
            $upstream_length_1 = $request->Upstream_Length;

            $motif = self::clean_malicious_input($motif);

            $gene = self::clean_malicious_input($gene);
            $gene = preg_replace('/\s+/', '', $gene);

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $motif_start = self::clean_malicious_input($motif_start);
            $motif_start = preg_replace("/[^0-9.]/", "", $motif_start);

            $motif_end = self::clean_malicious_input($motif_end);
            $motif_end = preg_replace("/[^0-9.]/", "", $motif_end);

            $gene_binding_sequence = self::clean_malicious_input($gene_binding_sequence);

            $upstream_length_1 = self::clean_malicious_input($upstream_length_1);
            $upstream_length_1 = preg_replace('/\s+/', '', $upstream_length_1);

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                $motif_table_name = "mViz_Rice_Japonica_Motif";
                $motif_sequence_table_name = "mViz_Rice_Japonica_" . $chromosome . "_Motif_Sequence";
                $tf_table_name = "mViz_Rice_Japonica_TF";
                $genotype_data_table_name = "mViz_Rice_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Rice_" . $chromosome . "_genotype_count";
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                $motif_table_name = "mViz_Arabidopsis_Motif";
                $motif_sequence_table_name = "mViz_Arabidopsis_" . $chromosome . "_Motif_Sequence";
                $tf_table_name = "mViz_Arabidopsis_TF";
                $genotype_data_table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_count";
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                $motif_table_name = "mViz_Maize_Motif";
                $motif_sequence_table_name = "mViz_Maize_" . $chromosome . "_Motif_Sequence";
                $tf_table_name = "mViz_Maize_TF";
                $genotype_data_table_name = "mViz_Maize_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Maize_" . $chromosome . "_genotype_count";
            }

            // Convert upstream length to integer
            if (is_string($upstream_length_1)) {
                $upstream_length_1 = preg_replace("/[^0-9.]/", "", $upstream_length_1);
                $upstream_length = intval(floatval(trim($upstream_length_1)));
            } elseif (is_int($upstream_length_1)) {
                $upstream_length = $upstream_length_1;
            } elseif (is_float($upstream_length_1)) {
                $upstream_length = intval($upstream_length_1);
            } else {
                $upstream_length = 2000;
            }

            $upstream_length = abs($upstream_length);

            if ($upstream_length > 6000) {
                $upstream_length = 6000;
            }

            $query_str = "
            SELECT M.Motif AS Binding_TF, TF.TF_Family, MS.Chromosome AS Binding_Chromosome,
            MS.Start AS Binding_Start, MS.End AS Binding_End, MS.Sequence AS Gene_Binding_Sequence,
            M.Gene, GFF.Chromosome, GFF.Start AS Gene_Start, GFF.End AS Gene_End, GFF.Strand AS Gene_Strand,
            GFF.Gene_Description, GROUP_CONCAT(DISTINCT GD.Position ORDER BY GD.Position ASC SEPARATOR ', ') AS Variant_Position
            FROM (
                SELECT Motif, Gene
                FROM " . $db . "." . $motif_table_name . "
                WHERE Motif = '" . $motif . "' AND Gene = '" . $gene . "'
            ) AS M
            LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
            ON M.Motif = TF.TF
            LEFT JOIN " . $db . "." . $motif_sequence_table_name . " AS MS
            ON M.Motif = MS.Name
            LEFT JOIN (
                SELECT ID, Name, Chromosome, Start, End, Strand, Gene_Description,
                CASE Strand
                    WHEN '+' THEN Start-1-" . $upstream_length_1 . "
                    ELSE End+1
                END AS Promoter_Start,
                CASE Strand
                    WHEN '+' THEN Start-1
                    ELSE End+1+" . $upstream_length_1 . "
                END AS Promoter_End
                FROM " . $db . "." . $table_name . "
                WHERE Chromosome = '" . $chromosome . "'
            ) AS GFF
            ON ((M.Gene = GFF.ID) AND (MS.Chromosome = GFF.Chromosome) AND (MS.Start BETWEEN GFF.Promoter_Start AND GFF.Promoter_End))
            LEFT JOIN " . $db . "." . $genotype_data_table_name . " AS GD
            ON ((MS.Chromosome = GD.Chromosome) AND (GD.Position BETWEEN MS.Start AND MS.End))
            WHERE ((GFF.Chromosome = '" . $chromosome . "') AND (MS.Start = " . $motif_start . ") AND (MS.End = " . $motif_end . "))
            GROUP BY M.Motif, TF.TF_Family, MS.Chromosome, MS.Start, MS.End, MS.Sequence, M.Gene, GFF.Chromosome, GFF.Start, GFF.End, GFF.Strand, GFF.Gene_Description
            ORDER BY MS.Chromosome, MS.Start, MS.End
            LIMIT 1;
            ";

            $query_str2 = "
            SELECT M.Motif AS Binding_TF, TF.TF_Family, MS.Chromosome AS Binding_Chromosome,
            MS.Start AS Binding_Start, MS.End AS Binding_End, MS.Sequence AS Gene_Binding_Sequence,
            M.Gene, GFF.Chromosome, GFF.Start AS Gene_Start, GFF.End AS Gene_End, GFF.Strand AS Gene_Strand,
            GFF.Gene_Description
            FROM (
                SELECT Motif, Gene
                FROM " . $db . "." . $motif_table_name . "
                WHERE Motif = '" . $motif . "' AND Gene = '" . $gene . "'
            ) AS M
            LEFT JOIN " . $db . "." . $tf_table_name . " AS TF
            ON M.Motif = TF.TF
            LEFT JOIN " . $db . "." . $motif_sequence_table_name . " AS MS
            ON M.Motif = MS.Name
            LEFT JOIN (
                SELECT ID, Name, Chromosome, Start, End, Strand, Gene_Description,
                CASE Strand
                    WHEN '+' THEN Start-1-" . $upstream_length_1 . "
                    ELSE End+1
                END AS Promoter_Start,
                CASE Strand
                    WHEN '+' THEN Start-1
                    ELSE End+1+" . $upstream_length_1 . "
                END AS Promoter_End
                FROM " . $db . "." . $table_name . "
                WHERE Chromosome = '" . $chromosome . "'
            ) AS GFF
            ON ((M.Gene = GFF.ID) AND (MS.Chromosome = GFF.Chromosome) AND (MS.Start BETWEEN GFF.Promoter_Start AND GFF.Promoter_End))
            WHERE ((GFF.Chromosome = '" . $chromosome . "') AND (MS.Start = " . $motif_start . ") AND (MS.End = " . $motif_end . "))
            ORDER BY MS.Chromosome, MS.Start, MS.End
            LIMIT 1;
            ";

            $binding_tf_result_arr = DB::connection($db)->select($query_str2);

            $query_str = "SELECT * ";
            $query_str = $query_str . "FROM " . $db . "." . $genotype_count_table_name . " ";
            $query_str = $query_str . "WHERE (Chromosome = '" . $chromosome . "') ";
            $query_str = $query_str . "AND (Position BETWEEN " . $motif_start . " AND " . $motif_end . ") ";
            $query_str = $query_str . "ORDER BY Chromosome, Position, Count DESC;";

            try {
                $genotype_count_result_arr = DB::connection($db)->select($query_str);
            } catch (\Throwable $e) {
                $genotype_count_result_arr = array();
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'Motif' => $motif,
                'Gene' => $gene,
                'Chromosome' => $chromosome,
                'Motif_Start' => $motif_start,
                'Motif_End' => $motif_end,
                'Gene_Binding_Sequence' => $gene_binding_sequence,
                'binding_tf_result_arr' => $binding_tf_result_arr,
                'genotype_count_result_arr' => $genotype_count_result_arr
            ];

            // Return to view
            return view('system/tools/GenVarX/viewPromoterOnSelectedBindingTF')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function ViewVariantAndPhenotypePage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->Chromosome;
            $position = $request->Position;
            $genotype = $request->Genotype;

            $chromosome = self::clean_malicious_input($chromosome);

            $position = self::clean_malicious_input($position);

            $genotype = self::clean_malicious_input($genotype);

            // Trim string
            if (is_string($chromosome)) {
                $chromosome = preg_replace('/\s+/', '', $chromosome);
            }
            if (is_string($position)) {
                $position = preg_replace("/[^0-9.]/", "", $position);
            }
            if (is_string($genotype)) {
                $genotype = trim($genotype);
            }

            // Table names
            if ($organism == "Osativa") {
                $genotype_data_table_name = "mViz_Rice_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Rice_" . $chromosome . "_genotype_count";
                $phenotype_selection_table_name = "mViz_Rice_Phenotype_Selection";
            } elseif ($organism == "Athaliana") {
                $genotype_data_table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_count";
                $phenotype_selection_table_name = "mViz_Arabidopsis_Phenotype_Selection";
            } elseif ($organism == "Zmays") {
                $genotype_data_table_name = "mViz_Maize_" . $chromosome . "_genotype_data";
                $genotype_count_table_name = "mViz_Maize_" . $chromosome . "_genotype_count";
                $phenotype_selection_table_name = "mViz_Maize_Phenotype_Selection";
            }

            // Query string
            $query_str = "SELECT * FROM " . $db . "." . $phenotype_selection_table_name . ";";

            try {
                $phenotype_selection_arr = DB::connection($db)->select($query_str);
            } catch (\Throwable $e) {
                $phenotype_selection_arr = array();
            }

            $query_str = "
                SELECT DISTINCT G.Genotype
                FROM " . $db . "." . $genotype_data_table_name . " AS G
                WHERE ((G.Chromosome = '" . $chromosome . "') AND (G.Position = " . $position . "));
            ";

            $genotype_selection_arr = DB::connection($db)->select($query_str);

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'chromosome' => $chromosome,
                'position' => $position,
                'genotype' => $genotype,
                'phenotype_selection_arr' => $phenotype_selection_arr,
                'genotype_selection_arr' => $genotype_selection_arr
            ];

            // Return to view
            return view('system/tools/GenVarX/viewVariantAndPhenotype')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function QueryVariantAndPhenotype(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        $chromosome = $request->Chromosome;
        $position = $request->Position;
        $genotype = $request->Genotype;
        $phenotype = $request->Phenotype;

        $chromosome = self::clean_malicious_input($chromosome);
        $chromosome = preg_replace('/\s+/', '', $chromosome);

        $position = self::clean_malicious_input($position);
        $position = preg_replace("/[^0-9.]/", "", $position);

        $genotype = self::clean_malicious_input($genotype);

        $phenotype = self::clean_malicious_input($phenotype);

        if (is_string($genotype)) {
            $genotype = trim($genotype);
            $temp_genotype_array = preg_split("/[;, \n]+/", $genotype);
            $genotype_array = array();
            for ($i = 0; $i < count($temp_genotype_array); $i++) {
                if (!empty(trim($temp_genotype_array[$i]))) {
                    array_push($genotype_array, trim($temp_genotype_array[$i]));
                }
            }
        } elseif (is_array($genotype)) {
            $temp_genotype_array = $genotype;
            $genotype_array = array();
            for ($i = 0; $i < count($temp_genotype_array); $i++) {
                if (!empty(trim($temp_genotype_array[$i]))) {
                    array_push($genotype_array, trim($temp_genotype_array[$i]));
                }
            }
        }

        if (is_string($phenotype)) {
            $phenotype = trim($phenotype);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotype);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotype)) {
            $temp_phenotype_array = $phenotype;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Table names
        if ($organism == "Osativa") {
            $genotype_data_table_name = "mViz_Rice_" . $chromosome . "_genotype_data";
            $phenotype_table_name = "mViz_Rice_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Rice_Accession_Mapping";
        } elseif ($organism == "Athaliana") {
            $genotype_data_table_name = "mViz_Arabidopsis_" . $chromosome . "_genotype_data";
            $phenotype_table_name = "mViz_Arabidopsis_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Arabidopsis_Accession_Mapping";
        } elseif ($organism == "Zmays") {
            $genotype_data_table_name = "mViz_Maize_" . $chromosome . "_genotype_data";
            $phenotype_table_name = "mViz_Maize_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Maize_Accession_Mapping";
        }

        // Construct query string
        $query_str = "SELECT GENO.Chromosome, GENO.Position, GENO.Accession, ";
        if ($organism == "Osativa") {
            $query_str = $query_str . "AM.Subpopulation, ";
        } elseif ($organism == "Athaliana") {
            $query_str = $query_str . "AM.Admixture_Group, ";
        } elseif ($organism == "Zmays") {
            $query_str = $query_str . "AM.Improvement_Status, ";
        }
        $query_str = $query_str . "GENO.Genotype, GENO.Category, GENO.Imputation ";
        if (isset($phenotype_array) && is_array($phenotype_array) && !empty($phenotype_array)) {
            for ($i = 0; $i < count($phenotype_array); $i++) {
                $query_str = $query_str . ", PH." . $phenotype_array[$i] . " ";
            }
        }
        $query_str = $query_str . "FROM ( ";
        $query_str = $query_str . "    SELECT G.Chromosome, G.Position, G.Accession, G.Genotype, G.Category, G.Imputation ";
        $query_str = $query_str . "    FROM " . $db . "." . $genotype_data_table_name . " AS G ";
        $query_str = $query_str . "    WHERE (G.Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "    AND (G.Position = " . $position . ") ";
        if (count($genotype_array) > 0) {
            $query_str = $query_str . "    AND (G.Genotype IN ('";
            for ($i = 0; $i < count($genotype_array); $i++) {
                if ($i < (count($genotype_array) - 1)) {
                    $query_str = $query_str . trim($genotype_array[$i]) . "', '";
                } elseif ($i == (count($genotype_array) - 1)) {
                    $query_str = $query_str . trim($genotype_array[$i]);
                }
            }
            $query_str = $query_str . "')) ";
        }
        $query_str = $query_str . ") AS GENO ";
        if (isset($phenotype_array) && is_array($phenotype_array) && !empty($phenotype_array)) {
            $query_str = $query_str . "LEFT JOIN " . $db . "." . $phenotype_table_name . " AS PH ";
            $query_str = $query_str . "ON CAST(GENO.Accession AS BINARY) = CAST(PH.Accession AS BINARY) ";
        }
        $query_str = $query_str . "LEFT JOIN " . $db . "." . $accession_mapping_table_name . " AS AM ";
        $query_str = $query_str . "ON CAST(GENO.Accession AS BINARY) = CAST(AM.Accession AS BINARY) ";
        $query_str = $query_str . "ORDER BY GENO.Chromosome, GENO.Position, GENO.Genotype; ";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }

    public function ViewVariantAndPhenotypeFiguresPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->chromosome_1;
            $position = $request->position_1;
            $genotype = $request->genotype_1;
            $phenotype = $request->phenotype_1;

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $position = self::clean_malicious_input($position);
            $position = preg_replace("/[^0-9.]/", "", $position);

            $genotype = self::clean_malicious_input($genotype);

            $phenotype = self::clean_malicious_input($phenotype);

            $genotype_array = array();
            if (is_string($genotype)) {
                $genotype = trim($genotype);
                $temp_genotype_array = preg_split("/[;, \n]+/", $genotype);
                $genotype_array = array();
                for ($i = 0; $i < count($temp_genotype_array); $i++) {
                    if (!empty(trim($temp_genotype_array[$i]))) {
                        array_push($genotype_array, trim($temp_genotype_array[$i]));
                    }
                }
            } elseif (is_array($genotype)) {
                $temp_genotype_array = $genotype;
                $genotype_array = array();
                for ($i = 0; $i < count($temp_genotype_array); $i++) {
                    if (!empty(trim($temp_genotype_array[$i]))) {
                        array_push($genotype_array, trim($temp_genotype_array[$i]));
                    }
                }
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'chromosome' => $chromosome,
                'position' => $position,
                'genotype_array' => $genotype_array,
                'phenotype' => $phenotype,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewVariantAndPhenotypeFigures')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function ViewAllCNVByGenesPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $gene_id_2 = $request->gene_id_2;
            $cnv_data_option = $request->cnv_data_option_2;

            $gene_id_2 = self::clean_malicious_input($gene_id_2);

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            $gene_arr = array();
            // Convert gene_id_2 string to array
            if (is_string($gene_id_2)) {
                $gene_id_2 = trim($gene_id_2);
                $temp_gene_arr = preg_split("/[;, \n]+/", $gene_id_2);
                $gene_arr = array();
                for ($i = 0; $i < count($temp_gene_arr); $i++) {
                    if (!empty(preg_replace('/\s+/', '', $temp_gene_arr[$i]))) {
                        array_push($gene_arr, preg_replace('/\s+/', '', $temp_gene_arr[$i]));
                    }
                }
            } elseif (is_array($gene_id_2)) {
                $temp_gene_arr = $gene_id_2;
                $gene_arr = array();
                for ($i = 0; $i < count($temp_gene_arr); $i++) {
                    if (!empty(preg_replace('/\s+/', '', $temp_gene_arr[$i]))) {
                        array_push($gene_arr, preg_replace('/\s+/', '', $temp_gene_arr[$i]));
                    }
                }
            }

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVS";
                }
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVS";
                }
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Maize_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Maize_CNVS";
                }
            }

            // Query gene information
            $query_str = "SELECT Chromosome, Start, End, Strand, Name AS Gene_ID, Gene_Description FROM " . $db . "." . $table_name;
            $query_str = $query_str . " WHERE (Name IN ('";
            for ($i = 0; $i < count($gene_arr); $i++) {
                if ($i < (count($gene_arr) - 1)) {
                    $query_str = $query_str . $gene_arr[$i] . "', '";
                } else {
                    $query_str = $query_str . $gene_arr[$i];
                }
            }
            $query_str = $query_str . "'));";

            $gene_result_arr = DB::connection($db)->select($query_str);

            // Query CNV information
            if (isset($gene_result_arr) && is_array($gene_result_arr) && !empty($gene_result_arr)) {
                $query_str = "SELECT Chromosome, Start, End, Width, Strand, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN0', 1, null)) AS CN0, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN1', 1, null)) AS CN1, ";
                if ($cnv_data_option == "Consensus_Regions") {
                    $query_str = $query_str . "COUNT(IF(CN = 'CN2', 1, null)) AS CN2, ";
                }
                $query_str = $query_str . "COUNT(IF(CN = 'CN3', 1, null)) AS CN3, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN4', 1, null)) AS CN4, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN5', 1, null)) AS CN5, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN6', 1, null)) AS CN6, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN7', 1, null)) AS CN7, ";
                $query_str = $query_str . "COUNT(IF(CN = 'CN8', 1, null)) AS CN8 ";
                $query_str = $query_str . "FROM " . $db . "." . $cnv_table_name . " WHERE ";

                for ($i = 0; $i < count($gene_result_arr); $i++) {
                    if ($i < (count($gene_result_arr) - 1)) {
                        $query_str = $query_str . "((Chromosome = '" . $gene_result_arr[$i]->Chromosome . "') AND (Start <= " . $gene_result_arr[$i]->Start . ") AND (End >= " . $gene_result_arr[$i]->End . ")) OR";
                    } elseif ($i == (count($gene_result_arr) - 1)) {
                        $query_str = $query_str . "((Chromosome = '" . $gene_result_arr[$i]->Chromosome . "') AND (Start <= " . $gene_result_arr[$i]->Start . ") AND (End >= " . $gene_result_arr[$i]->End . ")) ";
                    }
                }

                $query_str = $query_str . "GROUP BY Chromosome, Start, End, Width, Strand ";
                $query_str = $query_str . "ORDER BY Chromosome, Start, End;";

                $cnv_result_arr = DB::connection($db)->select($query_str);
            } else {
                $cnv_result_arr = NULL;
            }

            if (isset($gene_result_arr) && is_array($gene_result_arr) && !empty($gene_result_arr) && isset($cnv_result_arr) && is_array($cnv_result_arr) && !empty($cnv_result_arr)) {
                for ($i = 0; $i < count($cnv_result_arr); $i++) {
                    $query_str = "SELECT CNV.Chromosome, CNV.Start AS CNV_Start, CNV.End AS CNV_End, CNV.Width AS CNV_Width, CNV.Strand AS CNV_Strand, ";
                    $query_str = $query_str . "GFF.Start AS Gene_Start, GFF.End AS Gene_End, GFF.Strand AS Gene_Strand, GFF.Name AS Gene_Name, GFF.Gene_Description ";
                    $query_str = $query_str . "FROM ( ";
                    $query_str = $query_str . "SELECT DISTINCT Chromosome, Start, End, Width, Strand ";
                    $query_str = $query_str . "FROM " . $db . "." . $cnv_table_name . " WHERE ";
                    $query_str = $query_str . "(Chromosome = '" . $cnv_result_arr[$i]->Chromosome . "') AND (Start = " . $cnv_result_arr[$i]->Start . ") AND (End = " . $cnv_result_arr[$i]->End . ") ";
                    $query_str = $query_str . ") AS CNV ";
                    $query_str = $query_str . "LEFT JOIN " . $db . "." . $table_name . " AS GFF ON ";
                    $query_str = $query_str . "(CNV.Chromosome = GFF.Chromosome AND CNV.Start <= GFF.Start AND CNV.End >= GFF.End) ";
                    $query_str = $query_str . "ORDER BY CNV.Chromosome, CNV.Start, GFF.Start, GFF.End;";

                    $neighbouring_genes_result_arr = DB::connection($db)->select($query_str);

                    $cnv_result_arr[$i]->Neighbouring_Genes = $neighbouring_genes_result_arr;
                }
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'cnv_data_option' => $cnv_data_option,
                'gene_result_arr' => $gene_result_arr,
                'cnv_result_arr' => $cnv_result_arr
            ];

            // Return to view
            return view('system/tools/GenVarX/viewAllCNVByGenes')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function QueryPhenotypeDescription(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        // Table names
        if ($organism == "Osativa") {
            $table_name = "mViz_Rice_Phenotype_Selection";
        } elseif ($organism == "Athaliana") {
            $table_name = "mViz_Arabidopsis_Phenotype_Selection";
        } elseif ($organism == "Zmays") {
            $table_name = "mViz_Maize_Phenotype_Selection";
        }

        // Query string
        $query_str = "SELECT Phenotype, Phenotype_Description FROM " . $db . "." . $table_name . ";";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }


    public function QueryCNVAndPhenotype(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        $chromosome = $request->Chromosome;
        $position_start = $request->Start;
        $position_end = $request->End;
        $cnv_data_option = $request->Data_Option;
        $cn = $request->CN;
        $phenotype = $request->Phenotype;

        $chromosome = self::clean_malicious_input($chromosome);
        $chromosome = preg_replace('/\s+/', '', $chromosome);

        $position_start = self::clean_malicious_input($position_start);
        $position_start = preg_replace("/[^0-9.]/", "", $position_start);

        $position_end = self::clean_malicious_input($position_end);
        $position_end = preg_replace("/[^0-9.]/", "", $position_end);

        $cnv_data_option = self::clean_malicious_input($cnv_data_option);
        $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

        $cn = self::clean_malicious_input($cn);

        $phenotype = self::clean_malicious_input($phenotype);

        // Convert copy number string to array
        $cn_array = array();
        if (is_string($cn)) {
            $cn = trim($cn);
            $temp_cn_array = preg_split("/[;, \n]+/", $cn);
            $cn_array = array();
            for ($i = 0; $i < count($temp_cn_array); $i++) {
                if (!empty(trim($temp_cn_array[$i]))) {
                    array_push($cn_array, trim($temp_cn_array[$i]));
                }
            }
        } elseif (is_array($cn)) {
            $temp_cn_array = $cn;
            $cn_array = array();
            for ($i = 0; $i < count($temp_cn_array); $i++) {
                if (!empty(trim($temp_cn_array[$i]))) {
                    array_push($cn_array, trim($temp_cn_array[$i]));
                }
            }
        }

        // Convert phenotype string to array
        $phenotype_array = array();
        if (is_string($phenotype)) {
            $phenotype = trim($phenotype);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotype);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotype)) {
            $temp_phenotype_array = $phenotype;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Table names
        if ($organism == "Osativa") {
            $table_name = "mViz_Rice_Nipponbare_GFF";
            $phenotype_table_name = "mViz_Rice_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Rice_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Rice_Nipponbare_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Rice_Nipponbare_CNVS";
            }
        } elseif ($organism == "Athaliana") {
            $table_name = "mViz_Arabidopsis_GFF";
            $phenotype_table_name = "mViz_Arabidopsis_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Arabidopsis_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Arabidopsis_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Arabidopsis_CNVS";
            }
        } elseif ($organism == "Zmays") {
            $table_name = "mViz_Maize_GFF";
            $phenotype_table_name = "mViz_Maize_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Maize_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Maize_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Maize_CNVS";
            }
        }

        // Query string
        $query_str = "SELECT CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand, CNV.Accession, ";
        if ($organism == "Osativa") {
            $query_str = $query_str . "AM.Subpopulation, ";
        } elseif ($organism == "Athaliana") {
            $query_str = $query_str . "AM.Admixture_Group, ";
        } elseif ($organism == "Zmays") {
            $query_str = $query_str . "AM.Improvement_Status, ";
        }
        $query_str = $query_str . "CNV.CN, CNV.Status ";
        if (isset($phenotype_array) && is_array($phenotype_array) && !empty($phenotype_array)) {
            for ($i = 0; $i < count($phenotype_array); $i++) {
                $query_str = $query_str . ", PH." . $phenotype_array[$i] . " ";
            }
        }
        $query_str = $query_str . "FROM( ";
        $query_str = $query_str . "    SELECT C.Chromosome, C.Start, C.End, C.Width, C.Strand, C.Accession, C.CN, ";
        $query_str = $query_str . "    CASE C.CN ";
        $query_str = $query_str . "    WHEN 'CN0' THEN 'Loss' ";
        $query_str = $query_str . "    WHEN 'CN1' THEN 'Loss' ";
        $query_str = $query_str . "    WHEN 'CN3' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN4' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN5' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN6' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN7' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN8' THEN 'Gain' ";
        $query_str = $query_str . "    ELSE 'Normal' ";
        $query_str = $query_str . "    END as Status ";
        $query_str = $query_str . "    FROM " . $db . "." . $cnv_table_name . " AS C ";
        $query_str = $query_str . "    WHERE (C.Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "    AND (C.Start BETWEEN " . $position_start . " AND " . $position_end . ") ";
        $query_str = $query_str . "    AND (C.End BETWEEN " . $position_start . " AND " . $position_end . ") ";
        if (count($cn_array) > 0) {
            $query_str = $query_str . "    AND (C.CN IN ('";
            for ($i = 0; $i < count($cn_array); $i++) {
                if ($i < (count($cn_array) - 1)) {
                    $query_str = $query_str . trim($cn_array[$i]) . "', '";
                } elseif ($i == (count($cn_array) - 1)) {
                    $query_str = $query_str . trim($cn_array[$i]);
                }
            }
            $query_str = $query_str . "')) ";
        }
        $query_str = $query_str . ") AS CNV ";
        if (isset($phenotype_array) && is_array($phenotype_array) && !empty($phenotype_array)) {
            $query_str = $query_str . "LEFT JOIN " . $db . "." . $phenotype_table_name . " AS PH ";
            $query_str = $query_str . "ON CAST(CNV.Accession AS BINARY) = CAST(PH.Accession AS BINARY) ";
        }
        $query_str = $query_str . "LEFT JOIN " . $db . "." . $accession_mapping_table_name . " AS AM ";
        $query_str = $query_str . "ON CAST(CNV.Accession AS BINARY) = CAST(AM.Accession AS BINARY) ";
        $query_str = $query_str . "ORDER BY CNV.CN, CNV.Chromosome, CNV.Start, CNV.End, CNV.Accession; ";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }

    public function ViewCNVAndPhenotypePage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->Chromosome;
            $position_start = $request->Position_Start;
            $position_end = $request->Position_End;
            $cnv_data_option = $request->CNV_Data_Option;

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $position_start = self::clean_malicious_input($position_start);
            $position_start = preg_replace("/[^0-9.]/", "", $position_start);

            $position_end = self::clean_malicious_input($position_end);
            $position_end = preg_replace("/[^0-9.]/", "", $position_end);

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            // Table names
            if ($organism == "Osativa") {
                $phenotype_selection_table_name = "mViz_Rice_Phenotype_Selection";
            } elseif ($organism == "Athaliana") {
                $phenotype_selection_table_name = "mViz_Arabidopsis_Phenotype_Selection";
            } elseif ($organism == "Zmays") {
                $phenotype_selection_table_name = "mViz_Maize_Phenotype_Selection";
            }

            // Query string
            $query_str = "SELECT * FROM " . $db . "." . $phenotype_selection_table_name . ";";

            try {
                $phenotype_selection_arr = DB::connection($db)->select($query_str);
            } catch (\Throwable $e) {
                $phenotype_selection_arr = array();
            }


            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'chromosome' => $chromosome,
                'position_start' => $position_start,
                'position_end' => $position_end,
                'cnv_data_option' => $cnv_data_option,
                'phenotype_selection_arr' => $phenotype_selection_arr,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewCNVAndPhenotype')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }

    public function ViewCNVAndImprovementStatusPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->Chromosome;
            $position_start = $request->Position_Start;
            $position_end = $request->Position_End;
            $cnv_data_option = $request->CNV_Data_Option;

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $position_start = self::clean_malicious_input($position_start);
            $position_start = preg_replace("/[^0-9.]/", "", $position_start);

            $position_end = self::clean_malicious_input($position_end);
            $position_end = preg_replace("/[^0-9.]/", "", $position_end);

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'chromosome' => $chromosome,
                'position_start' => $position_start,
                'position_end' => $position_end,
                'cnv_data_option' => $cnv_data_option
            ];

            // Return to view
            return view('system/tools/GenVarX/viewCNVAndImprovementStatus')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }

    public function QueryCNVAndImprovementStatus(Request $request, $organism)
    {
        $organism = preg_replace('/\s+/', '', $organism);

        // Database
        $db = "KBC_" . $organism;

        $chromosome = $request->Chromosome;
        $position_start = $request->Start;
        $position_end = $request->End;
        $cnv_data_option = $request->Data_Option;

        $chromosome = self::clean_malicious_input($chromosome);
        $chromosome = preg_replace('/\s+/', '', $chromosome);

        $position_start = self::clean_malicious_input($position_start);
        $position_start = preg_replace("/[^0-9.]/", "", $position_start);

        $position_end = self::clean_malicious_input($position_end);
        $position_end = preg_replace("/[^0-9.]/", "", $position_end);

        $cnv_data_option = self::clean_malicious_input($cnv_data_option);
        $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

        // Table names
        if ($organism == "Osativa") {
            $table_name = "mViz_Rice_Nipponbare_GFF";
            $phenotype_table_name = "mViz_Rice_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Rice_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Rice_Nipponbare_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Rice_Nipponbare_CNVS";
            }
        } elseif ($organism == "Athaliana") {
            $table_name = "mViz_Arabidopsis_GFF";
            $phenotype_table_name = "mViz_Arabidopsis_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Arabidopsis_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Arabidopsis_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Arabidopsis_CNVS";
            }
        } elseif ($organism == "Zmays") {
            $table_name = "mViz_Maize_GFF";
            $phenotype_table_name = "mViz_Maize_Phenotype_Data";
            $accession_mapping_table_name = "mViz_Maize_Accession_Mapping";
            if ($cnv_data_option == "Consensus_Regions") {
                $cnv_table_name = "mViz_Maize_CNVR";
            } elseif ($cnv_data_option == "Individual_Hits") {
                $cnv_table_name = "mViz_Maize_CNVS";
            }
        }

        $query_str = "SELECT CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand, CNV.Accession, ";
        if ($organism == "Osativa") {
            $query_str = $query_str . "AM.Subpopulation, ";
        } elseif ($organism == "Athaliana") {
            $query_str = $query_str . "AM.Admixture_Group, ";
        } elseif ($organism == "Zmays") {
            $query_str = $query_str . "AM.Improvement_Status, ";
        }
        $query_str = $query_str . "CNV.CN, CNV.Status ";
        $query_str = $query_str . "FROM( ";
        $query_str = $query_str . "    SELECT C.Chromosome, C.Start, C.End, C.Width, C.Strand, C.Accession, C.CN, ";
        $query_str = $query_str . "    CASE C.CN ";
        $query_str = $query_str . "    WHEN 'CN0' THEN 'Loss' ";
        $query_str = $query_str . "    WHEN 'CN1' THEN 'Loss' ";
        $query_str = $query_str . "    WHEN 'CN3' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN4' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN5' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN6' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN7' THEN 'Gain' ";
        $query_str = $query_str . "    WHEN 'CN8' THEN 'Gain' ";
        $query_str = $query_str . "    ELSE 'Normal' ";
        $query_str = $query_str . "    END as Status ";
        $query_str = $query_str . "    FROM " . $db . "." . $cnv_table_name . " AS C ";
        $query_str = $query_str . "    WHERE (C.Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "    AND (C.Start BETWEEN " . $position_start . " AND " . $position_end . ") ";
        $query_str = $query_str . "    AND (C.End BETWEEN " . $position_start . " AND " . $position_end . ") ";
        $query_str = $query_str . ") AS CNV ";
        $query_str = $query_str . "LEFT JOIN " . $db . "." . $accession_mapping_table_name . " AS AM ";
        $query_str = $query_str . "ON CAST(CNV.Accession AS BINARY) = CAST(AM.Accession AS BINARY) ";
        $query_str = $query_str . "ORDER BY CNV.CN, CNV.Chromosome, CNV.Start, CNV.End; ";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }

    public function ViewCNVAndPhenotypeFiguresPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->chromosome_1;
            $position_start = $request->position_start_1;
            $position_end = $request->position_end_1;
            $width = $request->width_1;
            $strand = $request->strand_1;
            $cnv_data_option = $request->cnv_data_option_1;
            $phenotype = $request->phenotype_1;
            $cn = $request->cn_1;

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $position_start = self::clean_malicious_input($position_start);
            $position_start = preg_replace("/[^0-9.]/", "", $position_start);

            $position_end = self::clean_malicious_input($position_end);
            $position_end = preg_replace("/[^0-9.]/", "", $position_end);

            $width = self::clean_malicious_input($width);
            $width = preg_replace("/[^0-9.]/", "", $width);

            $strand = self::clean_malicious_input($strand);
            $strand = preg_replace('/\s+/', '', $strand);

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            $phenotype = self::clean_malicious_input($phenotype);

            $cn = self::clean_malicious_input($cn);

            // Convert copy number string to array
            if (is_string($cn)) {
                $cn = trim($cn);
                $temp_cn_array = preg_split("/[;, \n]+/", $cn);
                $cn_array = array();
                for ($i = 0; $i < count($temp_cn_array); $i++) {
                    if (!empty(trim($temp_cn_array[$i]))) {
                        array_push($cn_array, trim($temp_cn_array[$i]));
                    }
                }
            } elseif (is_array($cn)) {
                $temp_cn_array = $cn;
                $cn_array = array();
                for ($i = 0; $i < count($temp_cn_array); $i++) {
                    if (!empty(trim($temp_cn_array[$i]))) {
                        array_push($cn_array, trim($temp_cn_array[$i]));
                    }
                }
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'chromosome' => $chromosome,
                'position_start' => $position_start,
                'position_end' => $position_end,
                'width' => $width,
                'strand' => $strand,
                'cnv_data_option' => $cnv_data_option,
                'cn_array' => $cn_array,
                'phenotype' => $phenotype,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewCNVAndPhenotypeFigures')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }


    public function ViewAllCNVByAccessionAndCopyNumbersPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $accession = $request->accession_2;
            $copy_number_2 = $request->copy_number_2;
            $cnv_data_option = $request->cnv_data_option_2;

            $accession = self::clean_malicious_input($accession);
            $accession = preg_replace('/\s+/', '', $accession);

            $copy_number_2 = self::clean_malicious_input($copy_number_2);

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            // Convert copy number string to array
            $copy_number_arr = array();
            if (is_string($copy_number_2)) {
                $copy_number_2 = trim($copy_number_2);
                $temp_copy_number_arr = preg_split("/[;, \n]+/", $copy_number_2);
                $copy_number_arr = array();
                for ($i = 0; $i < count($temp_copy_number_arr); $i++) {
                    if (!empty(trim($temp_copy_number_arr[$i]))) {
                        array_push($copy_number_arr, trim($temp_copy_number_arr[$i]));
                    }
                }
            } elseif (is_array($copy_number_2)) {
                $temp_copy_number_arr = $copy_number_2;
                $copy_number_arr = array();
                for ($i = 0; $i < count($temp_copy_number_arr); $i++) {
                    if (!empty(trim($temp_copy_number_arr[$i]))) {
                        array_push($copy_number_arr, trim($temp_copy_number_arr[$i]));
                    }
                }
            }

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVS";
                }
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVS";
                }
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Maize_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Maize_CNVS";
                }
            }

            // Get CNV data
            $query_str = "SELECT CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand, CNV.Accession, CNV.CN ";
            $query_str = $query_str . "FROM " . $db . "." . $cnv_table_name . " AS CNV ";
            $query_str = $query_str . "WHERE (CAST(CNV.Accession AS BINARY) = CAST('" . $accession . "' AS BINARY)) AND (CNV.CN IN ('";
            for ($i = 0; $i < count($copy_number_arr); $i++) {
                if ($i < (count($copy_number_arr) - 1)) {
                    $query_str = $query_str . trim($copy_number_arr[$i]) . "', '";
                } elseif ($i == (count($copy_number_arr) - 1)) {
                    $query_str = $query_str . trim($copy_number_arr[$i]);
                }
            }
            $query_str = $query_str . "')) ";
            $query_str = $query_str . "ORDER BY CNV.CN, CNV.Chromosome, CNV.Start, CNV.End; ";

            $cnv_result_arr = DB::connection($db)->select($query_str);

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'accession' => $accession,
                'cnv_result_arr' => $cnv_result_arr,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewAllCNVByAccessionAndCopyNumbers')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }

    public function ViewAllCNVByChromosomeAndRegionPage(Request $request, $organism)
    {
        try {
            $organism = preg_replace('/\s+/', '', $organism);

            // Database
            $db = "KBC_" . $organism;

            $chromosome = $request->chromosome_2;
            $position_start = $request->position_start_2;
            $position_end = $request->position_end_2;
            $cnv_data_option = $request->cnv_data_option_2;

            $chromosome = self::clean_malicious_input($chromosome);
            $chromosome = preg_replace('/\s+/', '', $chromosome);

            $position_start = self::clean_malicious_input($position_start);
            $position_start = intval(preg_replace("/[^0-9.]/", "", $position_start)) - 1;

            $position_end = self::clean_malicious_input($position_end);
            $position_end = intval(preg_replace("/[^0-9.]/", "", $position_end)) + 1;

            $cnv_data_option = self::clean_malicious_input($cnv_data_option);
            $cnv_data_option = preg_replace('/\s+/', '', $cnv_data_option);

            // Table names
            if ($organism == "Osativa") {
                $table_name = "mViz_Rice_Nipponbare_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Rice_Nipponbare_CNVS";
                }
            } elseif ($organism == "Athaliana") {
                $table_name = "mViz_Arabidopsis_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Arabidopsis_CNVS";
                }
            } elseif ($organism == "Zmays") {
                $table_name = "mViz_Maize_GFF";
                if ($cnv_data_option == "Consensus_Regions") {
                    $cnv_table_name = "mViz_Maize_CNVR";
                } elseif ($cnv_data_option == "Individual_Hits") {
                    $cnv_table_name = "mViz_Maize_CNVS";
                }
            }

            $query_str = "SELECT CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN0', 1, null)) AS CN0, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN1', 1, null)) AS CN1, ";
            if ($cnv_data_option == "Consensus_Regions") {
                $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN2', 1, null)) AS CN2, ";
            }
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN3', 1, null)) AS CN3, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN4', 1, null)) AS CN4, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN5', 1, null)) AS CN5, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN6', 1, null)) AS CN6, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN7', 1, null)) AS CN7, ";
            $query_str = $query_str . "COUNT(IF(CNV.CN = 'CN8', 1, null)) AS CN8 ";
            $query_str = $query_str . "FROM " . $db . "." . $cnv_table_name . " AS CNV ";
            $query_str = $query_str . "WHERE (CNV.Chromosome = '" . $chromosome . "') ";
            $query_str = $query_str . "AND (CNV.Start BETWEEN " . $position_start . " AND " . $position_end . ") ";
            $query_str = $query_str . "AND (CNV.End BETWEEN " . $position_start . " AND " . $position_end . ") ";
            $query_str = $query_str . "GROUP BY CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand ";
            $query_str = $query_str . "ORDER BY CNV.Chromosome, CNV.Start, CNV.End; ";

            $cnv_accession_count_result_arr = DB::connection($db)->select($query_str);

            if (isset($cnv_accession_count_result_arr) && is_array($cnv_accession_count_result_arr) && !empty($cnv_accession_count_result_arr)) {
                $cnv_result_arr = array();
                for ($i = 0; $i < count($cnv_accession_count_result_arr); $i++) {
                    // Get CNV data
                    $query_str = "SELECT CNV.Chromosome, CNV.Start, CNV.End, CNV.Width, CNV.Strand, CNV.Accession, CNV.CN ";
                    $query_str = $query_str . "FROM " . $db . "." . $cnv_table_name . " AS CNV ";
                    $query_str = $query_str . "WHERE (CNV.Chromosome = '" . $cnv_accession_count_result_arr[$i]->Chromosome . "') ";
                    $query_str = $query_str . "AND (CNV.Start BETWEEN " . $cnv_accession_count_result_arr[$i]->Start . " AND " . $cnv_accession_count_result_arr[$i]->End . ") ";
                    $query_str = $query_str . "AND (CNV.End BETWEEN " . $cnv_accession_count_result_arr[$i]->Start . " AND " . $cnv_accession_count_result_arr[$i]->End . ") ";
                    $query_str = $query_str . "ORDER BY CNV.CN, CNV.Accession; ";

                    $result_arr = DB::connection($db)->select($query_str);

                    array_push($cnv_result_arr, $result_arr);
                }
            } else {
                $cnv_result_arr = NULL;
            }

            // Package variables that need to go to the view
            $info = [
                'organism' => $organism,
                'cnv_data_option' => $cnv_data_option,
                'cnv_accession_count_result_arr' => $cnv_accession_count_result_arr,
                'cnv_result_arr' => $cnv_result_arr,
            ];

            // Return to view
            return view('system/tools/GenVarX/viewAllCNVByChromosomeAndRegion')->with('info', $info);
        } catch (\Throwable $e) {
            abort(500);
        }
    }
}
