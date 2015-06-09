<?
    $DIR_PROG = dirname(realpath(__FILE__));
    include($DIR_PROG.'/libcom.php');
    ini_set('memory_limit', '2048M');

    $opt= getOption('l:x', array('di:','do:','dm:','wp','cmd'));
    foreach(array('l','dm','di','do') as $k)
    {
        if(!isset($opt[$k]))
            die(sprintf("\n !! %s -l LIST_PDB --dm DIR_OUT_MATRIX --di DIR_IN_PDB --do DIR_OUT_PDB [--wp]\n\n", $argv[0]));
    }

    $DEF_PARAM = parse_ini_file(sprintf('%s/0_param.ini', $DIR_PROG));

    $FN_LIST = getRealPath($opt['l']);
    $DIR_MAT = getRealPath($opt['dm']);
    $DIR_IN  = createPath($opt['di']);
    $DIR_OUT = createPath($opt['do']);
    
    $WHOLE_PDB = isset($opt['wp']) ? true : false;
    $SHOW_CMD = (isset($opt['x']) || isset($opt['cmd'])) ? true : false;

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
        $fn = sprintf('%s/%s', $DIR_IN, basename($fn));

        if(!file_exists($fn))
        {
            fprintf(STDERR, " [Error], No file (%s) for %s chain %s.\n", $fn, $id, $ch);
            continue;
        }

        $lst[] = array(
                    'id' => $id, 
                    'ch' => $ch, 
                    'fn' => $fn,
        );
    }

    $ref = $lst[0];
    $cmd = array();
    $cmd[] = sprintf("cd %s;\n", $DEF_PARAM['CE_DIR']);
    $cmd[] = sprintf('echo "# start to translate PDB files";'."\n");
    $num = count($lst);

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
   
        $c = sprintf(   "echo -ne \"# [%2d/%2d] %s %-20s\r\";php %s/3_getPDB.php -m %s -i %s -o %s.pdb -c %s --do %s;\n"
                        , $i+1, $num
                        , $tar['id'], ($WHOLE_PDB) ? '_' : $tar['ch']
                        , $DIR_PROG, $mfn, $tar['fn'], $tar['id']
                        , ($WHOLE_PDB) ? '_' : $tar['ch'], $DIR_OUT
        );
        $cmd[] = $c;
    }
    $cmd[] = sprintf('echo -e "\n# done";'."\n");

    if($SHOW_CMD)
    {
        echo join('',$cmd);
    }
    else
    {
        $fout = '.run.script';
        file_put_contents($fout,$cmd);
        //system("sh $fout"); // it cant show all raw outputs
        passthru("sh $fout"); 
    }

    exit;

?>
