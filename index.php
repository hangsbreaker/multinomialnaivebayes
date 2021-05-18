<?php
include "Multinomial.php";

// Proses awal dokumen
// inisialiasasi daftar kelas
$classlist = array("1" => "Cinta", "2" => "Motivasi", "3" => "Kehidupan");
// Dokumen yang telah di labeli kelas manual
$document = array(
    array("kalimat" => "Cinta itu indah", "id_kelas" => "1"),
    array("kalimat" => "Cinta ibu tulus", "id_kelas" => "1"),
    array("kalimat" => "Semangat jangan menyerah", "id_kelas" => "2"),
    array("kalimat" => "Senyum dan semangat", "id_kelas" => "2"),
    array("kalimat" => "Hidup seindah mimpi", "id_kelas" => "3"),
    array("kalimat" => "Bernafaslah agar hidup", "id_kelas" => "3"),
);

$class = array();
$kelas = array();
foreach ($classlist as $k => $v) {
    array_push($class, $k);
    $kelas[$k] = $v;
}

// ambil kalimat
$docs = array();
$docsc = array();
foreach ($document as $d) {
    array_push($docs, $d['kalimat']);
    array_push($docsc, $d['id_kelas']);
}


// start multinomial processing
$cl = new Multinomial();
// set dataset
$cl->class = $class;
$cl->docs = $docs;
$cl->docsc = $docsc;

// initial dataset with stemming / create probabilities
$cl->init();


echo '<b>Kelas</b> <pre>', print_r($classlist), '</pre>';
echo '<hr>';
echo '<b>Dokumen</b> <pre>', print_r($document), '</pre>';
echo '<hr>';

//echo '<pre>', print_r($cl->termProb), '</pre>';
foreach ($cl->probabily as $c => $term) {
    echo '<table class="table table-hover">
        <caption>Probabilitas Kelas : ' . $kelas[$c] . ' (' . $cl->classProb[$c] . ')' . '</caption>
        <thead>
            <tr>
                <th style="text-align:left;">Kata</th>
                <th style="text-align:right;">Tf</th>
                <th style="text-align:right;">Probailitas</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($term as $t => $v) {
        echo '<tr>
            <td>' . $t . '</td>
            <td style="text-align:right;">' . $cl->termFrek[$c][$t] . '</td>
            <td style="text-align:right;">' . $v . '</td>
        </tr>';
    }
    echo '</tbody></table><br>';
}

echo '<hr>';
// prediction process
$newdoc = "Cinta indah katanya";
$res = $cl->predict($newdoc, $cl->classProb, $cl->probabily);

echo 'Dokumen dicari kelasnya: <b>' . $newdoc . '</b><br>' . $cl->stem_step($newdoc) . '<br>';
echo '<pre>', print_r($res), '</pre>';
echo '<b><u>Dokumen tersebut termasuk kelas ' . $classlist[$cl->arr_key_first($res['class'])] . '</u></b>';

echo '<br><br><hr>';

// prediction process
$newdoc = "Semangat pantang menyerah";
$res = $cl->predict($newdoc, $cl->classProb, $cl->probabily);

echo 'Dokumen dicari kelasnya: <b>' . $newdoc . '</b><br>' . $cl->stem_step($newdoc) . '<br>';
echo '<pre>', print_r($res), '</pre>';
echo '<b><u>Dokumen tersebut termasuk kelas ' . $classlist[$cl->arr_key_first($res['class'])] . '</u></b>';
