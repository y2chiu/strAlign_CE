<?

	set_time_limit(0);

    function trans_structure($pname, $oname, $trmat, $set_ch='_', $set_lig='_')
    {
        if(($in = fopen($pname,'r')) === false)
        {
            fwrite(STDERR, "Open file ($pname) error!\n\n");
            return -1;
        }

        $RESORTSEQNUM = 0;

        $count_res = 0;
        $count_atm = 0;
                        
        $num_res   = 0;
        $num_atm   = 0;
        $name_res  = 'XXX';
        $name_atm  = 'XXX';
        $name_ch   = ' ';

        $isHET    = false;
        $hasRec   = false;
        $hasTer   = false;
        
        $setSelectCH  = false;
        $setSelectLIG = false;

        $select_ch   = array();
        $select_lig  = array();
        $conect_lig  = array();
                        
        $result = array('type'=>'X-Ray', 'str'=>array(), 'conect'=>array());
                

        if($set_ch != '_')
        {
            $setSelectCH = true;
            $select_ch = explode(',', $set_ch);
        }
        if($set_lig != '_')
        {
            $setSelectLIG = true;
            $select_lig = array_map('trim', explode(',', $set_lig));
        }

                
        while($line = fgets($in,4096)) 
        {
            $line    = trim($line);
            $record  = substr($line,0,6);
                        

            // Not PDB format
            if(empty($line))    return -1;
                        
            // For NMR only one Model; For X-Ray, if select chain
            if(	($hasRec || $hasTer) && (strncmp($record, 'ENDMDL', 6) == 0))
            {
                $result['type'] = 'NMR';
                break;
            }
            
            else if(strncmp($record, 'HET   ', 6) == 0)
            {
                $name_ch = substr($line, 12, 1); 
                if($setSelectCH && !in_array($name_ch, $select_ch))	continue;
                
                $out = $line."\n";
                $result['str'][] = $out;
            }


            else if(strncmp($record, 'TER', 3) == 0)
            {

                if(!$hasRec || $hasTer)	continue;
                else if($setSelectCH && !in_array($name_ch, $select_ch))	continue;

                $has_Ter = true;
                $has_Rec = false;

                if($RESORTSEQNUM)
                {
                    $len = strlen($line);
                    $name_res = ($len < 27) ? '' : substr($line, 17, 3);
                    $name_ch  = ($len < 21) ? '' : trim($line[21]);
                    $out = sprintf("%s%5s  %-4s%3s %s%4d\n",'TER   ',$count_atm+1,$name_res,$name_ch,$count_res);
                }
                else
                    $out = $line."\n";

                $result['str'][] = $out;
            }
            else if(strncmp($record, 'CONECT', 6) == 0)
            {
                $conect_atm = substr($line, 6, 5);
                if(array_key_exists($conect_atm, $conect_lig))
                    $result['conect'][] = $line."\n";

                continue;
            }

                        

            ////////////////////////////////////////
            //
            // filter non ATOM,HETATM records
            //
            if($record != 'ATOM  ' && $record != 'HETATM')
                continue;

            $name_res = substr($line, 17, 3);
            $name_atm = substr($line, 12, 4);
            $name_ch  = trim($line[21]);
            //$name_ch  = empty($name_ch) ? 'A' : $name_ch;
            $num_atm  = substr($line,  6, 5);
            $num_res  = substr($line, 22, 4);
            $altloc   = $line[16];
            $icode    = $line[26]; 
            
            $isHET   = $record[0] == 'H' ? true : false;

            // if choose specified chain
            if($setSelectCH && !in_array($name_ch, $select_ch))	continue;

            if($isHET && $hasTer /*&& $setSelectLIG*/)
            {
                //if(!in_array(trim($name_res), $select_lig))	continue;
                $conect_lig[$num_atm] = 1;
            }
            
            $hasRec = true;
            $hasTer = (!$isHET) ? false : true;	

            // 31 - 38   Real(8.3)     x           Orthogonal coordinates for X in Angstroms.
            // 39 - 46   Real(8.3)     y           Orthogonal coordinates for Y in Angstroms.
            // 47 - 54   Real(8.3)     z           Orthogonal coordinates for Z in Angstroms.
            // 55 - 60   Real(6.2)     occupancy   Occupancy. 
            // 61 - 66   Real(6.2)     tempFactor  Temperature factor.
            
            $count_atm++;
                                
            $x = doubleval(substr($line,30,8));
            $y = doubleval(substr($line,38,8));
            $z = doubleval(substr($line,46,8));
            $o = doubleval(substr($line,54,6));
            $b = doubleval(substr($line,60,6));
            
            $tr_x = $trmat[0]*$x+$trmat[1]*$y+$trmat[2]*$z+$trmat[ 9];
            $tr_y = $trmat[3]*$x+$trmat[4]*$y+$trmat[5]*$z+$trmat[10];
            $tr_z = $trmat[6]*$x+$trmat[7]*$y+$trmat[8]*$z+$trmat[11];

            list($x,$y,$z) = array($tr_x, $tr_y, $tr_z);

            $out = sprintf( "%s%5d %-4s%s%3s %s%4d%s   %8.3f%8.3f%8.3f%6.2f%6.2f\n"
                            , $record
                            , ($RESORTSEQNUM) ? $count_atm : $num_atm
                            , $name_atm
                            , $altloc
                            , $name_res
                            , $name_ch
                            , ($RESORTSEQNUM) ? $count_res : $num_res
                            , ($RESORTSEQNUM) ? ' ' : $icode
                            , $x, $y, $z, $o, $b
            ); 

            $result['str'][] = $out;

            unset($line);
        }
                        
        fclose($in);



        if(($fout = fopen($oname,'w')) === false)
        {
            fprintf(STDERR, " !! [Error] Open file error (%s).\n\n", $oname);
            return -1;
        }

        foreach($result['str'] as $line)
            fwrite($fout, $line);
        foreach($result['conect'] as $line)
            fwrite($fout, $line);

        fclose($fout);

        return;
    }

/*
    $mat = "
        1     -5.1411827447  -0.0499824528  -0.7903260110   0.6106443734
        2     68.1712710364   0.9276821786   0.1897730287   0.3215462226
        3    106.1473490700  -0.3700101759   0.5825555717   0.7236860341
    ";

    $mat = preg_split('/\s+/',trim($mat));

    $trm = array();
    for($i=0;$i<3;$i++)
        $trm = array_merge($trm,array_slice($mat,2+5*$i,3));
    $trm = array_merge($trm,array($mat[1],$mat[6],$mat[11]));


    $iname = '/research/dataset/pdb_ch/nn/2nntA.ent';
    $oname = basename(substr($iname,0,strrpos($iname,'.'))).'_tr.pdb';

    trans_structure($iname, $oname, $trm);
*/
?>
