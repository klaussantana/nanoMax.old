<?php
/**
 * Bootstrap
 *
 * O arquivo prepara o ambiente para execução do framework.
 *
 * @package      nanoMax
 * @subpackage   core
 * @category     Bootstrap
 * @author       Klauss Sant'Ana Guimarães
 * @copyright    Copyright (c) klaussantana.com
 * @license      http://nanoMax.klaussantana.com/#Licenciamento
 * @link         http://nanoMax.klaussantana.com
 * @version      0.1-dev
 * @filesource   
 **/

/**
 * Cabeçalhos HTTP
 *
 * Envia os cabeçalhos HTTP padrões do framework.
 **/
header('Content-type: text/html; charset=utf-8');
header('--nanoMax: nanoMax framework v0.1-dev (http://nanoMax.klaussantana.com)');
	
/**
 * Estrutura de pastas
 *
 * Prepara a estrutura de pastas para uso do framework.
 **/

/**
 * Objeto que conterá a estrutura de pastas
 *
 * @var stdClass Objeto em branco
 **/
global $NM_DIR;
$NM_DIR = new stdClass;

/**
 * Atalho para DIRECTORY_SEPARATOR
 *
 * @var string Contém o separador de pastas do sistema operacional
 **/
define('DS', DIRECTORY_SEPARATOR, TRUE);

/**
 * Definições das pastas
 **/
$NM_DIR ->root   = defined('NM_ROOT') ? NM_ROOT : realpath(dirname(__FILE__) .DS .'..');
$NM_DIR ->apps   = $NM_DIR->root .DS .'apps';    // Aplicativos
$NM_DIR ->core   = $NM_DIR->root .DS .'core';    // Núcleo do framework
$NM_DIR ->libs   = $NM_DIR->root .DS .'libs';    // Bibliotecas
$NM_DIR ->pub    = $NM_DIR->root .DS .'pub';     // Arquivos públicos (html,gif,jpg,etc...)
$NM_DIR ->tmp    = $NM_DIR->root .DS .'tmp';     // Arquivos temporários (uploads,etc...)
$NM_DIR ->var    = $NM_DIR->root .DS .'var';     // Arquivos variáveis (configurações,logs,etc...)
$NM_DIR ->shared = $NM_DIR->apps .DS .'.shared'; // Objetos compartilhados

/**
 * Criação e proteção das pastas
 **/
foreach ( $NM_DIR as $Directory )
{
	if ( realpath($Directory) === false || !is_dir($Directory) || !is_file($Directory .DS .'.htaccess') )
	{
		// Verifica a existência da pasta
		if ( !is_dir($Directory) )
		{
			$Creation = mkdir($Directory, 0744);
		}
		
		else
		{
			$Creation = true;
		}
		
		// Verifica a proteção da pasta
		if ( $Directory != $NM_DIR->root && $Directory != $NM_DIR->pub && !is_file($Directory .DS .'.htaccess') )
		{
			$Protection = file_put_contents($Directory .DS .'.htaccess', 'Deny from All');
		}
		
		// No caso da pasta 'pub', protege apenas contra listagem do conteúdo da pasta, excluindo subpastas
		elseif ( $Directory == $NM_DIR->pub && !is_file($Directory .DS .'index.html') )
		{
			$Protection = file_put_contents($Directory .DS .'index.html', '<!DOCTYPE html><html><body></body></html>');
		}
		
		else
		{
			$Protection = true;
		}
		
		// Verifica se foi criado e protegido
		if ( !$Creation || !$Protection )
		{
			die ("A instalação do framework está corrompida. A pasta '{$Directory}' não existe ou não pode ser protegido.");
		}
	}
}

/**
 * Importa a biblioteca contendo o objeto principal do framework.
 **/
if ( ($nanoMax = $NM_DIR->libs .DS .'nanoMax' .DS .'main.php') && is_file($nanoMax) && is_readable($nanoMax) )
{
	require_once $nanoMax;
}

else
{
	die ('Não foi possível carregar o framework. Verifique a instalação.');
}

/**
 * Registra os controladores de erro para o framework
 **/
set_error_handler( array('nanoMax', 'Trace') );
set_exception_handler( array('nanoMax', 'Trace') );

/**
 * Registra o framework para carregar automaticamente bibliotecas
 *
 * Obs.: `spl_autoload_register()` causa erro fatal por motivos ainda
 * desconhecidos. A função mágica `__autoload()` atende à demanda sem
 * erros nem lentidão.
 **/
function __autoload( $Library ) { nanoMax::Autoload($Library); }


/**
 * Apaga variáveis desnecessárias para o restante do programa
 **/
unset($Directory, $Creation, $Protection, $nanoMax, $ErrorHandler);

/**
 * Carrega e assimila automaticamente as bibliotecas ao framework
 **/
nanoMax::Autoload();

/**
 * Garante o tratamento da saída pelo framework
 **/
if ( ((strtolower(ini_get('implicit_flush')) =='off') && ob_get_level() >1) || ((strtolower(ini_get('implicit_flush')) =='on') && ob_get_level() >0) )
{
	die ('Erro interno. O tratamento da saída deve ser realizada sempre pelo framework.');
}

else
{
	ob_start( array('nanoMax', 'Output') );
}
?>