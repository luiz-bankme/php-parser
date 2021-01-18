<html>

<?php

include 'vendor/autoload.php';

$parser = new \Smalot\PdfParser\Parser();

$arrayAllCNPJ = array();

if (isset($_POST['send-file'])) :
  $allowedFormats = array("xml", "pdf");
  $arrayLength = count($_FILES['file']['name']);
  $counter = 0;

  while ($counter < $arrayLength) :
    $fileExtension = pathinfo($_FILES['file']['name'][$counter], PATHINFO_EXTENSION);

    if (in_array($fileExtension, $allowedFormats)) :
      $folderPDF = 'arquivos/pdf/';
      $folderPDFProblem = 'arquivos/pdf/problem/';
      $folderXML = 'arquivos/xml/';
      $temporary = $_FILES['file']['tmp_name'][$counter];
      $newName = uniqid() . ".$fileExtension";

      if ($fileExtension === 'pdf') :
        if (move_uploaded_file($temporary, $folderPDF . $newName)) :
          echo "Upload feito para $folderPDF.$newName <br>";
        else :
          echo "Erro ao enviar arquivo $temporary";
        endif;
        $pdf = $parser->parseFile("./$folderPDF$newName");
        $details  = $pdf->getDetails();

        $text = $pdf->getText();

        $cnpjAndCpfRegex = "/([0-9]{2}[\.][0-9]{3}[\.][0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2})|([0-9]{3}[\.][0-9]{3}[\.]?[0-9]{3}[-]?[0-9]{2})/";
        $emailRegex = "/(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})/i";

        if (preg_match_all($cnpjAndCpfRegex, $text, $cnpjOrCpf, PREG_PATTERN_ORDER)) :
          echo 'CNPJ CEDENTE: ';
          print_r($cnpjOrCpf[0][0]);
          $searchArray = array(".", "/", "-");
          $replaceArray = array("", "", "");
          $strCNPJCedente = str_replace($searchArray, $replaceArray, $cnpjOrCpf[0][0]);
          array_push($arrayAllCNPJ, $strCNPJCedente);
          $uniqueArrayCNPJ = array_unique($arrayAllCNPJ);
          echo '/CNPJ SACADO: ';
          print_r($cnpjOrCpf[0][1]);

        endif;


        if (!preg_match_all($cnpjAndCpfRegex, $text, $cnpjOrCpf, PREG_PATTERN_ORDER)) :

          if (rename($folderPDF . $newName, $folderPDFProblem . $newName)) :
            echo "Upload feito para $folderPDFProblem.$newName <br>";
          else :
            print_r(move_uploaded_file($temporary, $folderPDFProblem . $newName));
            echo "Erro ao enviar arquivo $temporary";
          endif;
          echo "<br>";
          echo 'ERRO CNPJ/CPF NÃO ENCONTRADO ';
          echo "<hr>";

        endif;
        if (preg_match_all($emailRegex, $text, $emailNota, PREG_PATTERN_ORDER)) :
          echo '/EMAIL: ';
          print_r($emailNota[0][0]);
          echo "<hr>";

        endif;

      // echo "$text <br>";

      // foreach ($details as $property => $value) {
      //   if (is_array($value)) {
      //     $value = implode(', ', $value);
      //   }
      //   echo $property . ' => ' . $value . "\n";
      // }
      // echo "<hr>";

      endif;

      if ($fileExtension === 'xml') :
        $cnpjAndCpfRegex = "/([0-9]{2}[\.]?[0-9]{3}[\.]?[0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2})|([0-9]{3}[\.]?[0-9]{3}[\.]?[0-9]{3}[-]?[0-9]{2})/";
        $emailRegex = "/(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})/i";

        if (move_uploaded_file($temporary, $folderXML . $newName)) :
          echo "Upload feito para $folderXML.$newName <br>";
        else :
          echo "Erro ao enviar arquivo $temporary";
        endif;
        $xml = simplexml_load_file("./$folderXML$newName") or die("Error: Cannot create object");

        $cnpjXmlEmit = (string)$xml->NFe->infNFe->emit->CNPJ[0];
        $nameXmlEmit = (string)$xml->NFe->infNFe->emit->xNome;
        $foneXmlEmit = (string)$xml->NFe->infNFe->emit->enderEmit->fone;
        $fantasyNameXmlEmit = (string) $xml->NFe->infNFe->emit->xFant;
        $cnpjXmlDest = (string)$xml->NFe->infNFe->dest->CNPJ;
        $nameXmlDest = (string)$xml->NFe->infNFe->dest->xNome;
        $foneXmlDest = (string) $xml->NFe->infNFe->dest->enderDest->fone;
        $cepXmlDest = (string)$xml->NFe->infNFe->dest->enderDest->CEP;
        $logradouroXmlDest = (string)$xml->NFe->infNFe->dest->enderDest->xLgr;
        $logradouroNumXmlDest = (string)$xml->NFe->infNFe->dest->enderDest->nro;
        $bairroXmlDest = (string) $xml->NFe->infNFe->dest->enderDest->xBairro;
        $municipioXmlDest = (string)$xml->NFe->infNFe->dest->enderDest->xMun;
        $ufXmlDest = (string)$xml->NFe->infNFe->dest->enderDest->UF;

        echo "<hr>";

        echo 'CNPJ EMIT: ';
        print_r($cnpjXmlEmit);
        echo "<br>";
        echo 'NAME EMIT: ';
        print_r($nameXmlEmit);
        echo "<br>";
        echo 'FANTASY NAME EMIT: ';
        print_r($fantasyNameXmlEmit);
        echo "<br>";
        echo 'FONE EMIT: ';
        print_r($foneXmlEmit);
        echo "<br>";
        echo "<br>";

        echo 'CNPJ DEST: ';
        print_r($cnpjXmlDest);
        echo "<br>";
        echo 'NAME DEST: ';
        print_r($nameXmlDest);
        echo "<br>";
        echo 'FONE DEST: ';
        print_r($foneXmlDest);
        echo "<br>";
        echo 'CEP DEST: ';
        print_r($cepXmlDest);
        echo "<br>";
        echo 'LOG DEST: ';
        print_r($logradouroXmlDest);
        echo "<br>";
        echo 'NUM DEST: ';
        print_r($logradouroNumXmlDest);
        echo "<br>";
        echo 'BAIRRO DEST: ';
        print_r($bairroXmlDest);
        echo "<br>";
        echo 'MUN DEST: ';
        print_r($municipioXmlDest);
        echo "<br>";
        echo 'UF DEST: ';
        print_r($ufXmlDest);
        echo "<br>";
        echo "<hr>";

      endif;

    else :
      echo "Extensão $fileExtension não é permitida. Não foi possível upload do arquivo. <br>";
      echo "<hr>";

    endif;

    $counter++;
  endwhile;
  foreach ($uniqueArrayCNPJ as $value) {
    $curl = curl_init();

    // Configura
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => "https://www.receitaws.com.br/v1/cnpj/{$value}"
    ]);

    // Envio e armazenamento da resposta
    $response = curl_exec($curl);

    print_r($response);

    // Fecha e limpa recursos
    curl_close($curl);
    echo "<hr>";
  }
endif;
?>
<style>
  form {
    width: 200px;
    max-width: 200px;
    margin-left: auto;
    margin-right: auto;
  }

  body {}

  input[type="file"] {
    display: none;
  }

  input[type="submit"] {
    display: none;
  }

  .custom-file-upload {
    border: 1px solid #ccc;
    border-radius: 8px;

    padding: 8px;
    display: inline-block;
    cursor: pointer;
    background-color: #F9F9F9;
  }

  .custom-file-upload-button {
    border: 1px solid #ccc;
    border-radius: 8px;

    padding: 6px;
    display: inline-block;
    cursor: pointer;
    background-color: #000E3D;
    margin-left: 4px;
    color: white;
  }

  input[type='submit'] {
    margin: 8px;
    background-color: green;

  }
</style>

<body>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
    <label class="custom-file-upload">
      <input type="file" name="file[]" multiple>
      Escolher arquivos
    </label>
    <label class="custom-file-upload-button">
      <input type="submit" name="send-file">
      Enviar
    </label>
  </form>
</body>

</html>