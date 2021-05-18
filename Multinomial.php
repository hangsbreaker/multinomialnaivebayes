<?php
require_once 'stemming.php';
class Multinomial
{

    var $class, $classProb, $docs, $docsc, $uniq, $term = array(), $docClass = array(), $termFrek = array(), $termProb = array(), $probabily = array(), $classpn = array();

    function stem($w)
    {
        $w = str_replace("\n", " ", $w);
        $w = " " . htmlspecialchars_decode(strtolower($w), ENT_QUOTES);
        $urlRegex = '~(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))~';
        $w = preg_replace($urlRegex, '', $w); // remove urls;
        $w = preg_replace('/(^|\s)@(\w+)/', '', $w); // remove @someone
        $w = preg_replace('/(^|\s)#(\w+)/', '', $w); // remove hashtags
        // remove 7 bit ASCII
        $w = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $w);
        $w = stemming($w);
        $w = $this->removeunusedchar(" " . $w . " ");
        return trim($w);
    }

    function stem_step($w)
    {
        $step = "<h3>Proses Preprosesing</h3><hr>";
        $w = str_replace("\n", " ", $w);
        $step .= '<label>Hapus Newline</label><br>' . $w . '<br><br>';
        $w = " " . htmlspecialchars_decode(strtolower($w), ENT_QUOTES);
        $step .= '<label>Menjadikan Lowecase</label><br>' . $w . '<br><br>';
        $urlRegex = '~(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))~';
        $w = preg_replace($urlRegex, '', $w); // remove urls;
        $step .= '<label>Hapus Link</label><br>' . $w . '<br><br>';
        $w = preg_replace('/(^|\s)@(\w+)/', '', $w); // remove @someone
        $step .= '<label>Hapus Username</label><br>' . $w . '<br><br>';
        $w = preg_replace('/(^|\s)#(\w+)/', '', $w); // remove hashtags
        $step .= '<label>Hapus Hashtag</label><br>' . $w . '<br><br>';
        // remove 7 bit ASCII
        $w = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $w);
        $step .= '<label>Hapus Unicode Character</label><br>' . $w . '<br><br>';
        $w = stemming($w);
        $step .= '<label>Stemming</label><br>' . $w . '<br><br>';
        $w = $this->removeunusedchar(" " . $w . " ");
        $step .= '<label>Stopword</label><br>' . trim($w) . '<br><br>';
        return $step;
    }

    function init()
    {
        $dclss = array();
        foreach ($this->class as $c) {
            $dclss[$c] = 0;
        }
        $this->class = $dclss;
        $this->classProb = $this->class;
        $this->classpn = $this->class;
        $jdocls = array_count_values($this->docsc);
        foreach ($jdocls as $c => $j) {
            $this->classpn[$c] = $j;
            $this->classProb[$c] = $j / count($this->docs);
        }

        foreach ($this->docs as $k => $d) {
            $out = explode(" ", $this->stem($d));

            $this->class[$this->docsc[$k]] = $this->class[$this->docsc[$k]] + count($out);

            if (!array_key_exists($this->docsc[$k], $this->docClass)) {
                $this->docClass[$this->docsc[$k]] = array();
            }
            $this->docClass[$this->docsc[$k]] = array_merge($this->docClass[$this->docsc[$k]], $out);

            $this->term = array_unique(array_merge($this->term, $out));
        }

        $this->uniq = count($this->term);

        foreach ($this->class as $c => $j) {
            $arrcount = array_count_values($this->docClass[$c]);
            foreach ($this->term as $t) {
                $jc = (array_key_exists($t, $arrcount)) ? $arrcount[$t] : 0;
                $this->probabily[$c][$t] = (($jc + 1) / ($j + $this->uniq));
                $this->termFrek[$c][$t] = $jc;

                //echo $t . ' ((' . $jc . ' + 1) / (' . $j . ' + ' . $this->uniq . ')) = ' . (($jc + 1) / ($j + $this->uniq)) . ' <br>';
            }
            //echo '<br>';
            $this->probabily[$c]['{none}'] = ((0 + 1) / ($j + $this->uniq));
            $this->termFrek[$c]['{none}'] = $jc;
        }

        foreach ($this->probabily as $c => $term) {
            foreach ($term as $t => $v) {
                $this->termProb[$t][$c] = $v;
            }
        }

        return $this->probabily;
    }

    // predict
    function predict($w, $prior = null, $prob = null)
    {
        $str = array();
        $classProb = array();
        if ($prior == null && $prob == null) {
            $classProb = $this->classProb;
            $probabilitas = $this->probabily;
        } else {
            $classProb = $prior;
            $probabilitas = $prob;
        }
        $kalimat = $this->stem($w);
        $out = explode(" ", $kalimat);
        $classnone = 1;
        foreach ($classProb as $c => $v) {
            $none = 0;
            $exists = 0;
            $str[$c] = $c . ' = ' . $classProb[$c] . ' * ';

            $classnone = $classProb[$c];
            foreach ($out as $k) {
                if (array_key_exists($k, $probabilitas[$c])) {
                    $str[$c] .= $probabilitas[$c][$k] . ' * ';
                    $classProb[$c] = $classProb[$c] * $probabilitas[$c][$k];
                    $exists++;
                } else {
                    $str[$c] .= $probabilitas[$c]['{none}'] . ' * ';
                    $classProb[$c] = $classProb[$c] * $probabilitas[$c]['{none}'];
                    $classnone = $classnone * $probabilitas[$c]['{none}'];
                    $none++;
                }
            }
            $str[$c] = substr($str[$c], 0, -3);
            $str[$c] .=  ' = ' . $classProb[$c];
        }

        //echo $none . '__ ' . $exists . '<br>';//$none > ($exists) || 
        if (count($out) < 3 || strlen($kalimat) < 15) {
            sort($classProb);
        } else {
            arsort($classProb);
        }
        $frst = $this->arr_key_first($classProb);
        if ($classnone > $classProb[$frst]) {
            //echo "A";
        }
        return array("class" => $classProb, "calculate" => $str);
    }

    function removeunusedchar($str)
    {
        $delimiters = array("\r\n", "\"", "\"", "\\", "“", "”", "”", "&ldquo;", "&rdquo;", "'", "_", "-", "+", "(", ")", "#", "<", ">", "/", " ", ",", ".", "|", ":", ";", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "!", "?", "=", "%", "&", "@", "*", "[", "]", "{", "}", "^", "~", "â", "€", "–", "ª", "ª", "˜º", "ï", "¸", "ð", "ÿ", "˜‡", "»", "‘", "˜", "…", "š", "«", "™", "¤", "¢", "¬", "©", "‰", " a ", " b ", " c ", " d ", " e ", " f ", " g ", " h ", " i ", " j ", " k ", " l ", " m ", " n ", " o ", " p ", " q ", " r ", " s ", " t ", " u ", " v ", " w ", " x ", " y ", " z ", "&#039;", " ada ", " adalah ", " adanya ", " adapun ", " agak ", " agaknya ", " agar ", " akan ", " akankah ", " akhiri ", " akhirnya ", " aku ", " akulah ", " amat ", " amatlah ", " anda ", " andalah ", " antar ", " antara ", " antaranya ", " apa ", " apaan ", " apabila ", " apakah ", " apalagi ", " apatah ", " artinya ", " asal ", " asalkan ", " atas ", " atau ", " ataukah ", " ataupun ", " awal ", " awalnya ", " bagai ", " bagaikan ", " bagaimana ", " bagaimanakah ", " bagaimanapun ", " bagi ", " bagian ", " bahkan ", " bahwa ", " bahwasanya ", " #NAME? ", " bakal ", " bakalan ", " balik ", " banyak ", " bapak ", " baru ", " bawah ", " beberapa ", " begini ", " beginian ", " beginikah ", " beginilah ", " begitu ", " begitukah ", " begitulah ", " begitupun ", " bekerja ", " belakang ", " belakangan ", " belumlah ", " #NAME? ", " benarkah ", " benarlah ", " berada ", " berakhir ", " berakhirlah ", " berakhirnya ", " berapa ", " berapakah ", " berapalah ", " #NAME? ", " berarti ", " berawal ", " berbagai ", " berdatangan ", " #NAME? ", " berikan ", " berikut ", " berikutnya ", " berjumlah ", " berkali-kali ", " berkata ", " berkehendak ", " berkeinginan ", " berkenaan ", " berlainan ", " berlalu ", " berlangsung ", " berlebihan ", " bermacam ", " bermacam-macam ", " bermaksud ", " bermula ", " bersama ", " bersama-sama ", " bersiap ", " bersiap-siap ", " bertanya ", " bertanya-tanya ", " berturut ", " berturut-turut ", " bertutur ", " berujar ", " berupa ", " #NAME? ", " betul ", " betulkah ", " biasa ", " biasanya ", " bila ", " bilakah ", " bisa ", " bisakah ", " boleh ", " bolehkah ", " bolehlah ", " #NAME? ", " bukan ", " bukankah ", " bukanlah ", " bukannya ", " bulan ", " bung ", " cara ", " caranya ", " cukup ", " cukupkah ", " cukuplah ", " cuma ", " dahulu ", " dalam ", " dan ", " dapat ", " dari ", " daripada ", " datang ", " dekat ", " demi ", " demikian ", " demikianlah ", " dengan ", " depan ", " di ", " dia ", " diakhiri ", " diakhirinya ", " dialah ", " diantara ", " diantaranya ", " diberi ", " diberikan ", " diberikannya ", " #NAME? ", " dibuatnya ", " didapat ", " didatangkan ", " digunakan ", " diibaratkan ", " diibaratkannya ", " #NAME? ", " diingatkan ", " diinginkan ", " dijawab ", " dijelaskan ", " dijelaskannya ", " dikarenakan ", " dikatakan ", " dikatakannya ", " dikerjakan ", " diketahui ", " diketahuinya ", " dikira ", " dilakukan ", " dilalui ", " dilihat ", " dimaksud ", " dimaksudkan ", " dimaksudkannya ", " dimaksudnya ", " diminta ", " dimintai ", " dimisalkan ", " dimulai ", " dimulailah ", " dimulainya ", " dimungkinkan ", " dini ", " dipastikan ", " diperbuat ", " diperbuatnya ", " dipergunakan ", " diperkirakan ", " diperlihatkan ", " diperlukan ", " diperlukannya ", " dipersoalkan ", " dipertanyakan ", " dipunyai ", " diri ", " dirinya ", " disampaikan ", " disebut ", " disebutkan ", " disebutkannya ", " disini ", " disinilah ", " ditambahkan ", " ditandaskan ", " ditanya ", " ditanyai ", " ditanyakan ", " ditegaskan ", " ditujukan ", " ditunjuk ", " ditunjuki ", " ditunjukkan ", " ditunjukkannya ", " ditunjuknya ", " dituturkan ", " dituturkannya ", " #NAME? ", " #NAME? ", " diungkapkan ", " dong ", " dua ", " #NAME? ", " empat ", " enggak ", " enggaknya ", " entah ", " entahlah ", " gunakan ", " hal ", " hampir ", " hanya ", " hanyalah ", " hari ", " #NAME? ", " haruslah ", " harusnya ", " hendak ", " hendaklah ", " hendaknya ", " hingga ", " ia ", " ialah ", " ibarat ", " ibaratkan ", " ibaratnya ", " ibu ", " ikut ", " #NAME? ", " ingat-ingat ", " ingin ", " inginkah ", " inginkan ", " ini ", " inikah ", " inilah ", " itu ", " itukah ", " itulah ", " #NAME? ", " jadilah ", " jadinya ", " #NAME? ", " jangankan ", " janganlah ", " jauh ", " jawab ", " jawaban ", " jawabnya ", " jelas ", " jelaskan ", " jelaslah ", " jelasnya ", " jika ", " jikalau ", " juga ", " jumlah ", " jumlahnya ", " justru ", " kala ", " kalau ", " kalaulah ", " kalaupun ", " kalian ", " kami ", " kamilah ", " kamu ", " kamulah ", " kan ", " kapan ", " kapankah ", " kapanpun ", " karena ", " karenanya ", " kasus ", " #NAME? ", " katakan ", " katakanlah ", " katanya ", " ke ", " keadaan ", " kebetulan ", " kecil ", " kedua ", " keduanya ", " keinginan ", " kelamaan ", " kelihatan ", " kelihatannya ", " kelima ", " keluar ", " kembali ", " kemudian ", " kemungkinan ", " kemungkinannya ", " kenapa ", " kepada ", " kepadanya ", " kesampaian ", " keseluruhan ", " keseluruhannya ", " keterlaluan ", " #NAME? ", " khususnya ", " kini ", " kinilah ", " kira ", " kira-kira ", " kiranya ", " kita ", " kitalah ", " kok ", " kurang ", " lagi ", " lagian ", " lah ", " lain ", " lainnya ", " lalu ", " lama ", " lamanya ", " lanjut ", " lanjutnya ", " lebih ", " lewat ", " lima ", " luar ", " macam ", " maka ", " makanya ", " makin ", " malah ", " malahan ", " mampu ", " mampukah ", " mana ", " manakala ", " manalagi ", " #NAME? ", " masalah ", " masalahnya ", " masih ", " masihkah ", " masing ", " masing-masing ", " mau ", " maupun ", " melainkan ", " #NAME? ", " #NAME? ", " melihat ", " melihatnya ", " memang ", " memastikan ", " memberi ", " #NAME? ", " #NAME? ", " memerlukan ", " memihak ", " meminta ", " memintakan ", " memisalkan ", " memperbuat ", " mempergunakan ", " memperkirakan ", " memperlihatkan ", " mempersiapkan ", " mempersoalkan ", " mempertanyakan ", " mempunyai ", " memulai ", " memungkinkan ", " menaiki ", " menambahkan ", " menandaskan ", " menanti ", " menanti-nanti ", " menantikan ", " menanya ", " menanyai ", " menanyakan ", " mendapat ", " mendapatkan ", " mendatang ", " mendatangi ", " mendatangkan ", " menegaskan ", " mengakhiri ", " mengapa ", " mengatakan ", " mengatakannya ", " mengenai ", " mengerjakan ", " mengetahui ", " menggunakan ", " menghendaki ", " mengibaratkan ", " mengibaratkannya ", " #NAME? ", " #NAME? ", " menginginkan ", " mengira ", " mengucapkan ", " mengucapkannya ", " mengungkapkan ", " #NAME? ", " menjawab ", " menjelaskan ", " menuju ", " menunjuk ", " menunjuki ", " menunjukkan ", " menunjuknya ", " menurut ", " menuturkan ", " menyampaikan ", " menyangkut ", " menyatakan ", " menyebutkan ", " menyeluruh ", " menyiapkan ", " merasa ", " mereka ", " merekalah ", " merupakan ", " meski ", " meskipun ", " meyakini ", " meyakinkan ", " minta ", " mirip ", " misal ", " misalkan ", " misalnya ", " mula ", " mulai ", " mulailah ", " mulanya ", " mungkin ", " mungkinkah ", " nah ", " naik ", " namun ", " nanti ", " nantinya ", " nyaris ", " nyatanya ", " oleh ", " olehnya ", " pada ", " padahal ", " padanya ", " pak ", " paling ", " panjang ", " pantas ", " para ", " pasti ", " pastilah ", " penting ", " pentingnya ", " per ", " percuma ", " perlu ", " perlukah ", " perlunya ", " #NAME? ", " persoalan ", " pertama ", " pertama-tama ", " pertanyaan ", " pertanyakan ", " pihak ", " pihaknya ", " pukul ", " pula ", " pun ", " punya ", " rasa ", " rasanya ", " rata ", " rupanya ", " saat ", " saatnya ", " saja ", " sajalah ", " saling ", " sama ", " sama-sama ", " sambil ", " sampai ", " sampai-sampai ", " sampaikan ", " sana ", " sangat ", " sangatlah ", " satu ", " saya ", " sayalah ", " se ", " sebab ", " sebabnya ", " sebagai ", " sebagaimana ", " sebagainya ", " sebagian ", " sebaik ", " sebaik-baiknya ", " sebaiknya ", " sebaliknya ", " sebanyak ", " sebegini ", " sebegitu ", " sebelum ", " sebelumnya ", " sebenarnya ", " seberapa ", " sebesar ", " sebetulnya ", " sebisanya ", " sebuah ", " sebut ", " sebutlah ", " sebutnya ", " secara ", " secukupnya ", " sedang ", " sedangkan ", " sedemikian ", " sedikit ", " sedikitnya ", " seenaknya ", " segala ", " segalanya ", " segera ", " seharusnya ", " sehingga ", " seingat ", " sejak ", " sejauh ", " sejenak ", " sejumlah ", " sekadar ", " sekadarnya ", " sekali-kali ", " sekalian ", " sekaligus ", " sekalipun ", " sekarang ", " sekarang ", " sekecil ", " seketika ", " sekiranya ", " sekitar ", " sekitarnya ", " sekurang-kurangnya ", " sekurangnya ", " sela ", " selain ", " selaku ", " selalu ", " selama ", " selama-lamanya ", " selamanya ", " selanjutnya ", " seluruh ", " seluruhnya ", " semacam ", " semakin ", " semampu ", " semampunya ", " semasa ", " semasih ", " semata ", " semata-mata ", " semaunya ", " sementara ", " semisal ", " semisalnya ", " sempat ", " semua ", " semuanya ", " semula ", " sendiri ", " sendirian ", " sendirinya ", " seolah ", " seolah-olah ", " seorang ", " sepanjang ", " sepantasnya ", " sepantasnyalah ", " seperlunya ", " seperti ", " sepertinya ", " sepihak ", " sering ", " seringnya ", " serta ", " serupa ", " sesaat ", " sesama ", " sesampai ", " sesegera ", " sesekali ", " #NAME? ", " sesuatu ", " sesuatunya ", " sesudah ", " sesudahnya ", " setelah ", " setempat ", " setengah ", " seterusnya ", " setiap ", " setiba ", " setibanya ", " setidak-tidaknya ", " setidaknya ", " setinggi ", " seusai ", " sewaktu ", " siap ", " siapa ", " siapakah ", " siapapun ", " sini ", " sip ", " sinilah ", " soal ", " soalnya ", " suatu ", " sudah ", " sudahkah ", " sudahlah ", " supaya ", " tadi ", " tadinya ", " tahu ", " tahun ", " tak ", " tambah ", " tambahnya ", " tampak ", " tampaknya ", " tandas ", " tandasnya ", " tanpa ", " tanya ", " tanyakan ", " tanyanya ", " tapi ", " tegas ", " tegasnya ", " telah ", " tempat ", " tengah ", " tentang ", " tentu ", " tentulah ", " tentunya ", " #NAME? ", " terakhir ", " terasa ", " terbanyak ", " terdahulu ", " terdapat ", " terdiri ", " terhadap ", " terhadapnya ", " #NAME? ", " teringat-ingat ", " terjadi ", " terjadilah ", " terjadinya ", " terkira ", " terlalu ", " terlebih ", " terlihat ", " termasuk ", " ternyata ", " tersampaikan ", " tersebut ", " tersebutlah ", " tertentu ", " tertuju ", " terus ", " terutama ", " tetapi ", " tiap ", " tiba ", " tiba-tiba ", " tidak ", " tidakkah ", " tidaklah ", " tiga ", " tinggi ", " toh ", " tunjuk ", " turut ", " tutur ", " tuturnya ", " #NAME? ", " ucapnya ", " ujar ", " ujarnya ", " umum ", " umumnya ", " ungkap ", " ungkapnya ", " untuk ", " usah ", " usai ", " waduh ", " wah ", " wahai ", " waktu ", " waktunya ", " walau ", " walaupun ", " wong ", " yaitu ", " yakin ", " yakni ", " yang ", " a ", " b ", " c ", " d ", " aca ", " bos ", " donk ", " aja ", " al ", " cak ", " cuy ", " dah ", " dear ", " dll ", " dunkz ", " ea ", " ga ", " i ", " oke ", " yup ", " yg ", " mungkin ", " ya ", " yaa ", " tdk ", " ta ", " terima ", " terima ", " tolong ", " gak ", " gk ", " mohon ", " ndak ", " assalamualaikum ", " sih ", " iya ", " iiya ", " pusing ", " nah ", " benah ", " dibenahi ", " orang ", " dibenarkan ", " pada ", " thanx ", " bro ", " wr ", " wb ", " Alhamdulillah ", " dengan ", " lambat ", " lemot ", " ojo ", " pelit ", " kog ", " kyk ", " wb ", " mengaku ", " lo ", "mention", " mu ", " quotes ", " by ", " tv ", " aktif ", " kudu ", " tp ", " browser ", " org ", " dh ", " knp ", " post ", " in ", " gt ", " #NAME? ", " mbak ", " mas ", " aq ", " mentionan ", " tangan ", " ali ", "theres", "there", "called", "call", "reply", "suruh", "quote", "quot", "retweet", "tweet");
        $ready = str_replace("  ", " ", str_replace($delimiters, " ", str_replace($delimiters, " ", $str)));
        return  $ready;
    }

    function arr_key_first(array $array)
    {
        foreach ($array as $key => $value) {
            return $key;
        }
    }
}
