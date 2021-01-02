<?php
// STEMMING ========================
function dasar($kata)
{
    if (
        (substr($kata, -3) == "kah" && strlen($kata) > 7) || (strlen($kata) > 5 && substr($kata, -3) == "lah") || substr($kata, -3) == "pun"
    ) {
        $kata = substr($kata, -3);
    }
    //langkah 2 - hapus possesive pronoun
    if (strlen($kata) > 4) {
        if (substr($kata, -2) == "ku" || substr($kata, -2) == "mu") {
            $kata = substr($kata, 0, strlen($kata) - 2);
        } else {
            if (substr($kata, -5) == "annya") {
                $kata = substr($kata, 0, strlen($kata) - 5);
            } else {
                if (substr($kata, -3) == "nya") {
                    $kata = substr($kata, 0, strlen($kata) - 3);
                }
            }
        }
    }
    //langkah 3 hapus first order prefiks (awalan pertama)
    if (substr($kata, 0, 2) == "me") {
        if (substr($kata, 0, 3) == "mem") {
            if (
                substr($kata, 3, 1) == "a" ||
                substr($kata, 3, 1) == "i" ||
                substr($kata, 3, 1) == "e" ||
                substr($kata, 3, 1) == "u" ||
                substr($kata, 3, 1) == "o"
            ) {
                if (
                    //(substr($kata,4, 1) == "s" && substr($kata,5, 1) != "t") ||
                    ((substr($kata, 4, 1) == "l" ||
                        substr($kata, -1) == "m" ||
                        substr($kata, -1) == "k") &&
                        substr($kata, -1) != "n") || (substr($kata, -1) == "n" && strlen($kata) <= 7)
                ) {
                    $kata = "m" . substr($kata, 3, strlen($kata));
                } else {
                    $kata = "p" . substr($kata, 3, strlen($kata));
                }
            } else {
                $kata = substr($kata, 3, strlen($kata));
            }
        } else {
            if (substr($kata, 0, 3) == "men") {
                if (substr($kata, 3, 1) == "e" || substr($kata, 3, 1) == "a") {
                    $kata = "t" . substr($kata, 3, strlen($kata));
                } else if (substr($kata, 3, 1) == "i") {
                    $kata = "n" . substr($kata, 3, strlen($kata));
                } else {
                    if (substr($kata, 0, 4) == "meng") {
                        if (
                            substr($kata, 4, 1) == "a" ||
                            substr($kata, 4, 1) == "e" ||
                            substr($kata, 4, 1) == "o" ||
                            substr($kata, 4, 1) == "u"
                        ) {
                            if (
                                (strlen(suffix(substr($kata, 4, strlen($kata)))) > 5 &&
                                    substr($kata, -1) != "t") ||
                                substr($kata, -1) == "p"
                            ) {
                                $kata = "k" . substr($kata, 4, strlen($kata));
                            } else {
                                $kata = substr($kata, 4, strlen($kata));
                            }
                        } else {
                            $kata = substr($kata, 4, strlen($kata));
                        }
                    } else {
                        if (substr($kata, 0, 4) == "meny") {
                            $kata = "s" . substr($kata, 4, strlen($kata));
                        } else {
                            $kata = substr($kata, 3, strlen($kata));
                        }
                    }
                }
            } else {
                if (strlen($kata) > 5) {
                    $kata = substr($kata, 0, 2);
                }
            }
        }
    } else {
        if (substr($kata, 0, 4) == "peng") {
            if (substr($kata, 4, 1) == "e") {
                $kata = "k" . substr($kata, 4, strlen($kata));
            } else {
                $kata = substr($kata, 4, strlen($kata));
            }
        } else {
            if (substr($kata, 0, 3) == "pem") {
                if (substr($kata, 6, 1) != "r") {
                    if (
                        substr($kata, 3, 1) == "a" ||
                        substr($kata, 3, 1) == "i" ||
                        substr($kata, 3, 1) == "e" ||
                        substr($kata, 3, 1) == "u" ||
                        substr($kata, 3, 1) == "o"
                    ) {
                        $kata = "p" . substr($kata, 3, strlen($kata));
                    } else {
                        $kata = substr($kata, 3, strlen($kata));
                    }
                } else {
                    $kata = "m" . substr($kata, 3, strlen($kata));
                }
            } else {
                if (substr($kata, 0, 3) == "pen") {
                    if (
                        substr($kata, 3, 1) == "a" ||
                        substr($kata, 3, 1) == "i" ||
                        substr($kata, 3, 1) == "e" ||
                        substr($kata, 3, 1) == "u" ||
                        substr($kata, 3, 1) == "o"
                    ) {
                        $kata = "t" . substr($kata, 3, strlen($kata));
                    } else {
                        $kata = substr($kata, 3, strlen($kata));
                    }
                } else {
                    if (substr($kata, 0, 4) == "peny") {
                        $kata = "s" . substr($kata, 4, strlen($kata));
                    } else {
                        if (substr($kata, 0, 2) == "di") {
                            $kata = substr($kata, 2, strlen($kata));
                        } else {
                            if (substr($kata, 0, 3) == "ter") {
                                $kata = substr($kata, 3, strlen($kata));
                            } else {
                                if (substr($kata, 0, 2) == "ke") {
                                    if (substr($kata, 2, 1) == "m") {
                                        //$$kata = substr($$kata,3);
                                    } else {
                                        if (substr($kata, 2, 1) != "n") {
                                            $kata = substr($kata, 2, strlen($kata));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    //langkah 4 hapus second order prefiks (awalan kedua)
    if (substr($kata, 0, 2) == "be") {
        if (substr($kata, 2, 1) == "k") {
            $kata = substr($kata, 2, strlen($kata));
        } else {
            if (substr($kata, 0, 3) == "bel") {
                $kata = substr($kata, 3, strlen($kata));
            } else {
                if (substr($kata, 0, 3) == "ber") {
                    if (
                        substr($kata, 3, 3) == "sih" ||
                        substr($kata, 3, 3) == "ani" ||
                        substr($kata, 3, 4) == "ikan"
                    ) { } else {
                        if (substr($kata, 3, 1) == "i") {
                            $kata = substr($kata, 2, strlen($kata));
                        } else {
                            $kata = substr($kata, 3, strlen($kata));
                        }
                    }
                }
            }
        }
    } else {
        if (substr($kata, 0, 3) == "per" && strlen(substr($kata, 3, strlen($kata))) > 5) {
            $kata = substr($kata, 3, strlen($kata));
        } else {
            if (substr($kata, 0, 3) == "pel" && strlen($kata) > 5) {
                $kata = substr($kata, 3, strlen($kata));
            } else {
                if (substr($kata, 0, 2) == "se" && strlen($kata) > 5) {
                    if (substr($kata, 2, 1) == "t") {
                        $kata = substr($kata, 2, strlen($kata));
                    }
                } else {
                    if (substr($kata, 0, 2) == "pe" && strlen(substr($kata, 0, 2)) > 5) {
                        if (substr($kata, 2, 1) == "r" && strlen(substr($kata, 0, 3)) > 5) {
                            $kata = substr($kata, 3, strlen($kata));
                        } else {
                            if (substr($kata, 2, 1) == "l" && strlen($kata) > 5) {
                                if (substr($kata, 4, 1) == "y") {
                                    $kata = substr($kata, 2, strlen($kata));
                                } else {
                                    $kata = substr($kata, 3, strlen($kata));
                                }
                            } else {
                                if (strlen(suffix(substr($kata, 2, strlen($kata)))) > 4) {
                                    $kata = substr($kata, 2, strlen($kata));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $kata = suffix($kata);
    ////langkah 5 hapus kalau cuma 1 karakter
    if (strlen($kata) == 1) {
        $kata = "";
    }
    return $kata;
}

function suffix($kata)
{
    ////langkah 5 hapus suffiks
    $prefix = array("kah", "lah", "pun", "me", "mem", "men", "meng", "meny", "peng", "pem", "pen", "peny", "di", "ter", "ke", "kem", "ken", "be", "bek", "bel", "ber", "per", "pel", "se");
    $str = str_replace($prefix, "", $kata);

    if (substr($kata, -1) == "i") {
        if (strlen($str) > 5) {
            if (substr($kata, strlen($kata) - 3, 2) != "rt") {
                $kata = substr($kata, 0, strlen($kata) - 1);
            }
        }
    } else {
        if (substr($kata, -2) == "an") {
            if (substr($kata, -3) != "ian") {
                if (substr($kata, -3) == "kan") {
                    if (substr($kata, -4) == "ikan") {
                        if (substr($kata, -4, 1) == "p") {
                            $kata = substr($kata, -3) . "an";
                        } else {
                            if (substr($kata, -4, 1) == "l") {
                                $kata = substr($kata, 0, strlen($kata) - 3);
                            } else {
                                $kata = substr($kata, 0, strlen($kata) - 3);
                            }
                        }
                    } else {
                        if (strlen($kata) > 5) {
                            $kata = substr($kata, 0, strlen($kata) - 3);
                        }
                    }
                } else {
                    $kata = substr($kata, 0, strlen($kata) - 2);
                }
            }
        }
    }
    return $kata;
}

function stemming($kalimat)
{
    if ($kalimat != "") {
        $str = "";
        $ark = explode(" ", strtolower(str_replace('/[^0-9a-zA-Z]/g', " ", $kalimat)));
        foreach ($ark as $kata) {
            $str = $str . " " . dasar($kata);
        }
        return trim($str);
    }
    return;
};
