<?
    $DIR_PROG = dirname(realpath(__FILE__));
    include($DIR_PROG.'/libcom.php');
    ini_set('memory_limit', '2048M');

    $opt= getOption('l:', array('di:','do:'));
    foreach(array('l','di','do') as $k)
    {
        if(!isset($opt[$k]))
            die(sprintf("\n !! %s -l LIST_PDB --di DIR_INPUT_PDB --do DIR_OUT_MATRIX\n\n", $argv[0]));
    }

    $DEF_PARAM = parse_ini_file(sprintf('%s/0_param.ini', $DIR_PROG));

    $FN_LIST = getRealPath($opt['l']);
    $DIR_IN  = createPath($opt['di']);
    $DIR_OUT = createPath($opt['do']);

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
    $cmd[] = sprintf('echo "# start CE alignment";');
    $num = count($lst);

    foreach($lst as $i => $tar)
    {
        $c = sprintf(   "echo -ne \"# [%2d/%2d] %s %s to %s %s\r\";./CErun - %s %s %s %s scratch > %s/%s-%s.cemat\n"
                        , $i+1, $num
                        , $tar['id'], $tar['ch'], $ref['id'], $ref['ch']
                        , $ref['fn'], $ref['ch'] 
                        , $tar['fn'], $tar['ch'] 
                        , $DIR_OUT, $tar['id'], $ref['id']
        );
        $cmd[] = $c;
    }

    $cmd[] = sprintf('echo "# finish CE alignment";');
    $cmd[] = sprintf("cd -;\n");

    foreach($cmd as $c)
        echo $c;

    exit;

?>
