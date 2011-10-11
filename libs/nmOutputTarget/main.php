<?php
/**
 * nmOutputTarget (biblioteca)
 * 
 * A biblioteca detecta informações sobre como o resultado da aplicação
 * será exibida para o usuário. Se será exibido em um console (cmd, bash,
 * etc), num navegador (Chrome, Firefox, etc.), se será transmitido por
 * tecnologia Ajax (XMLHTTPRequest, jQuery, etc.) ou se será exibido por
 * um dispositivo móvel (celular, tablet, etc.).
 * 
 * A biblioteca também dispõe de um método para detectar se o navegador
 * do usuário tem a capacidade de receber conteúdo comprimido.
 * 
 * NOTA: Para que esta biblioteca possa detectar se a pilha de saída
 * será destinada a um dispositivo móvel é necessário que a biblioteca
 * `nmBrowscap` ou uma equivalente esteja assimilada em `nanoMax`.
 * 
 * NOTA: Esta biblioteca não pode ser instanciada e nem clonada.
 * 
 * Exemplo:
 * ========
 * <?php
 *    nanoMax::Assembly('nmOutputTarget');
 *    
 *    if ( nanoMax::OutputForConsole() )
 *       echo 'Você está utilizando o console.';
 *    
 *    if ( nanoMax::OutputForBrowser() )
 *       echo 'Você está utilizando o navegador.';
 *    
 *    if ( nanoMax::OutputForMobile() )
 *       echo 'Você está utilizando um dispositivo móvel.';
 *    
 *    if ( nanoMax::OutputForAjax() )
 *       echo 'Requisição realizada por ajax.';
 *    
 *    // Se for possível comprimir/compactar a saída, registra um manipulador de saída
 *    if ( nanoMax::CanCompressOutput() )
 *       ob_start( array('nanoMax', 'CompressOutput') );
 * ?>
 * 
 * @package      nanoMax
 * @subpackage   nmOutputTarget
 * @category     Library-Target-Detector
 * @author       Klauss Sant'Ana Guimarães
 * @copyright    Copyright (c) klaussantana.com
 * @license      http://www.gnu.org/licenses/lgpl.html LGPL - LESSER GENERAL PUBLIC LICENSE
 * @link         http://nanoMax.klaussantana.com
 * @version      0.1-dev
 * @filesource   
 **/
class nmOutputTarget extends nmGear
{
	/**
	 * Para aonde a pilha de saída (buffer) será enviada.
	 *
	 * @static
	 * @access   private
	 * @var      array     Alvo da pilha de saída.
	 **/
	static private $Target =false;
	
	/**
	 * Construtor da classe
	 *
	 * Não é possível instanciar esta classe deliberadamente.
	 *
	 * @access   private
	 **/
	private
	function __construct()
	{}
	
	/**
	 * Clonador da classe
	 *
	 * Não é possível clonar esta classe.
	 *
	 * @access   private
	 **/
	private
	function __clone()
	{}
	
	/**
	 * Realiza a verificação do alvo da pilha de saída.
	 * 
	 * NOTA: Se o método falhar e não for possível detectar
	 * qual a verdadeira fonte da solicitação será retornado
	 * `browser` por padrão.
	 * 
	 * NOTA: Para detecção de dispositivos móveis é necessário
	 * que a biblioteca `nmBrowscap` ou uma equivalente esteja
	 * assimilada em `nanoMax` implementando o método
	 * `nanoMax::GetBrowser()`.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   string   `ajax`, `browser`, `console` ou `mobile`*.
	 **/
	static
	public
	function DetectOutputTarget()
	{
		// Se `STDOUT` está definido e é um recurso, output para console
		if ( defined('STDOUT') && is_resource(STDOUT) )
		{
			static::$Target = 'console';
		}
		
		// Se a requisição foi realizada por um XMLHttpRequest, output para console
		else if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) =='xmlhttprequest') )
		{
			static::$Target = 'ajax';
		}
		
		// se o framework possuir a detecção de browser, verifica se é dispositivo móvel
		else if ( is_callable( array('nanoMax', 'GetBrowser') ) && ( ($Browser = nanoMax::GetBrowser()) && (int)($Browser['ismobiledevice']) ) )
		{
			static::$Target = 'mobile';
		}
		
		// Demais casos, output para browser
		else
		{
			static::$Target = 'browser';
		}
	}
	
	/**
	 * Verifica se o alvo da pilha de saída é um console.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   bool     Se é ou não um console.
	 **/
	static
	public
	function OutputForConsole()
	{
		if ( static::$Target =='console' )
		{
			return true;
		}
		
		else
		{
			return false;
		}
	}
	
	/**
	 * Verifica se o alvo da pilha de saída é ajax.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   bool     Se é ou não ajax.
	 **/
	static
	public
	function OutputForAjax()
	{
		if ( static::$Target =='ajax' )
		{
			return true;
		}
		
		else
		{
			return false;
		}
	}
	
	/**
	 * Verifica se o alvo da pilha de saída é um navegador.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   bool     Se é ou não um navegador.
	 **/
	static
	public
	function OutputForBrowser()
	{
		if ( static::$Target =='browser' )
		{
			return true;
		}
		
		else
		{
			return false;
		}
	}
	
	/**
	 * Verifica se o alvo da pilha de saída é um dispositivo móvel.
	 * 
	 * NOTA: Para detecção de dispositivos móveis é necessário
	 * que a biblioteca `nmBrowscap` ou uma equivalente esteja
	 * assimilada em `nanoMax` implementando o método
	 * `nanoMax::GetBrowser()`.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   bool     Se é ou não um dispositivo móvel.
	 **/
	static
	public
	function OutputForMobile()
	{
		if ( static::$Target =='mobile' )
		{
			return true;
		}
		
		else
		{
			return false;
		}
	}
	
	/**
	 * Verifica se o alvo da pilha de saída tem capacidade
	 * de receber conteúdo comprimido.
	 * 
	 * @static
	 * @access   public
	 * @param    void     Este método não recebe parâmetros.
	 * @return   mixed    Se suporta `gzip`, retornará `gzip`, caso contrário, `false`.
	 **/
	static
	public
	function CanCompressOutput()
	{
		$AcceptEncodings = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
		
		if ( (static::OutputForBrowser() || static::OutputForAjax()) && in_array('gzip', $AcceptEncodings) && function_exists('gzencode') )
		{
			return 'gzip';
		}
		
		else
		{
			return false;
		}
	}
	
	/**
	 * Comprime a pilha de saída e a retorna.
	 * 
	 * NOTA: Este método já escreve os cabeçalhos HTTP com as
	 * informações de `Content-encoding` e `Content-length`.
	 * 
	 * NOTA: Se o alvo da pilha de saída não suportar compressão
	 * então o método irá enviar os cabeçalhos HTTP compatíveis e
	 * o conteúdo da pilha de saída sem processamento.
	 * 
	 * @static
	 * @access   public
	 * @param    string   $Buffer - A pilha de saída original.
	 * @return   string   A pilha de saída processada.
	 **/
	static
	public
	function CompressOutput( $Buffer )
	{
		// Se for possível enviar conteúdo comprimido
		if ( static::CanCompressOutput() =='gzip' )
		{
			$Output = gzencode($Buffer);
			$Length = function_exists('mb_strlen') ? mb_strlen($Output, 'utf-8') : strlen($Output);
			
			header('Content-encoding: gzip');
			header('Content-length: ' .$Length);
			
			trigger_error('nmOutputTarget: ' .static::Language()->CompressedWithGzip);
			
			return $Output;
		}
		
		// Se não for possível enviar conteúdo comprimido
		else
		{
			$Length = function_exists('mb_strlen') ? mb_strlen($Buffer, 'utf-8') : strlen($Buffer);
			
			header('Content-length: ' .$Length);
			
			trigger_error('nmOutputTarget: ' .static::Language()->CantCompress);
			
			return $Buffer;
		}
	}
	
	/**
	 * Retorna as linguagens padrões
	 * 
	 * @see nmGear::DefaultLanguage()
	 **/
	static
	public
	function DefaultLanguage( $Context =null, $Language ='pt', $Family ='br' )
	{
		$Languages = array();
		$Language  = strtolower($Language);
		$Family    = strtolower($Family);
		
		// Portugês do Brasil
		$LanguageXML  = '<Language>';
		$LanguageXML .= '   <CompressedWithGzip>A saída para o usuário foi comprimida utilizando `gzip`.</CompressedWithGzip>';
		$LanguageXML .= '   <CantCompress>Não foi possível comprimir a saída para o usuário. Foi enviado o conteúdo original.</CantCompress>';
		$LanguageXML .= '</Language>';
		
		$Languages['pt_br'] = new SimpleXMLElement($LanguageXML);
		
		// Inglês
		$LanguageXML  = '<Language>';
		$LanguageXML .= '   <CompressedWithGzip>The output was compressed with `gzip`.</CompressedWithGzip>';
		$LanguageXML .= '   <CantCompress>Can not compress the output. Raw data sent.</CantCompress>';
		$LanguageXML .= '</Language>';
		
		$Languages['en']    = 
		$Languages['en_gb'] = 
		$Languages['en_us'] = new SimpleXMLElement($LanguageXML);
		
		if ( empty($Family) )
		{
			$LanguageCode = $Language;
		}
		
		else
		{
			$LanguageCode = "{$Language}_{$Family}";
		}
		
		if ( isset($Languages[$LanguageCode]) )
		{
			return $Languages[$LanguageCode];
		}
		
		else if ( isset($Languages[$Language]) )
		{
			trigger_error("nmOutputTarget: Não foi possível adquirir a linguagem '{$LanguageCode}'. Utilizado '{$Language}'.");
			return $Languages[$Language];
		}
		
		else
		{
			trigger_error("nmOutputTarget: Não foi possível adquirir a linguagem '{$LanguageCode}'. Utilizado 'pt_br'.");
			return $Languages['pt_br'];
		}
	}
}
?>