<?
    $DIR_PROG = dirname(realpath(__FILE__));
    include($DIR_PROG.'/libcom.php');
    ini_set('memory_limit', '2048M');

    $opt= getOption('l:', array('di:','do:','dm:'));
    foreach(array('l','dm') as $k)
    {
        if(!isset($opt[$k]))
            die(sprintf("\n !! %s -l LIST_PDB --dm DIR_OUT_MATRIX\n\n", $argv[0]));
    }

    $DEF_PARAM = parse_ini_file(sprintf('%s/0_param.ini', $DIR_PROG));

    $FN_LIST = getRealPath($opt['l']);
    $DIR_MAT = getRealPath($opt['dm']);

    // INPUT FORMAT
    // using first line as reference
    // for example, 3h9r chain A will be aligned to 2jdr chain A
    //
    //#ID     CHAIN   FILE_NAME
    //2jdr_A  A       30_kinase_cav/AKT2_AGC_2jdr_A.pdb
    //3h9r_A  A       30_kinase_cav/ACVR1_TKL_3h9r_A.pdb

    $lst = array();
    $fin = file($FN_LIST, FILE_IGNORE_NEW_LINES);
    foreach($fin as $ln)
    {
        if(empty($ln) || $ln[0] == '#') 
            continue;

        list($id, $ch, $fn) = preg_split("/\s+/", $ln);

        $lst[] = array(
                    'id' => $id, 
                    'ch' => $ch, 
                    'fn' => $fn,
        );
    }

    $ref = $lst[0];
    $result = array();

    foreach($lst as $i => $tar)
    {
        $mfn = sprintf('%s/%s-%s.cemat', $DIR_MAT, $tar['id'], $ref['id']);
        if(!file_exists($mfn))
        {
            fprintf(STDERR  , " [Error], No MATRIX file (%s) for %s chain %s to %s chain %s.\n"
                            , $mfn
                            , $tar['id'], $tar['ch'], $ref['id'], $ref['ch']
            );
            continue;
        }
    
        $cmd = sprintf('cat %s | head -n 9', $mfn);
        $out = explode("\n",trim(shell_exec($cmd)));
   

        if(count($out) != 8)
        {
            fprintf(STDERR  , " [Error], No available information in MATRIX file (%s).\n"
                            , $mfn
            );
            continue;
        }

        $out = array_slice($out, -4);

        $refi = preg_split("/[\(=\s\)]+/", $out[0]);
        $tari = preg_split("/[\(=\s\)]+/", $out[1]);
        $alni = preg_split("/[\(=\s\)]+/", $out[3]);

        $rlen = $refi[4];
        $tlen = $tari[4];
        list(,,$alen,,$rmsd,,$zs,,$gap) = $alni;
        $rmsd = substr($rmsd, 0, -1);

        $d = array($tar['id'], $tar['ch'], $tlen, $ref['id'], $ref['ch'], $rlen, $alen, $rmsd, $zs, $gap);
        $result[] = $d;
    }

    usort($result, 'cmp');

    echo join("\t", array('#TARGET','TAR_CHAIN','TAR_LENGTH','REFERENCE','REF_CHAIN','REF_LENGTH', 'Aligned_Length','RMSD','ZScore','Gap')), "\n";
    foreach($result as $d)
        echo join("\t", $d), "\n";

    exit;

    function cmp($a, $b)
    {
        return $a[7] > $b[7];
    }

?>
