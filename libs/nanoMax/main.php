<?php
/**
 * nanoMax (biblioteca)
 *
 * É a biblioteca principal do framework.
 * 
 * A classe funciona como um agregador de bibliotecas, tornando-a
 * por fim um objeto pseudo-polimórfico, sobrecarregável, ou seja,
 * que tem a capacidade de se transformar a medida que novas
 * bibliotecas são agregadas.
 * 
 * Exemplo:
 * ========
 * <?php
 *    class MinhaClasse
 *    {
 *       public $MinhaVariavel = 'Minha variável';
 *       
 *       public function MeuMetodo ()
 *       {
 *          return 'Meu método.';
 *       }
 *    }
 *    
 *    nanoMax::Assembly('MinhaClasse');
 *    echo '<br/> nanoMax incorporou MinhaClasse.';
 *    echo '<br/> nanoMax::GetProperty("MinhaVariavel") = ' .nanoMax::GetProperty('MinhaVariavel');
 *    echo '<br/> nanoMax::MeuMetodo() = ' .nanoMax::MeuMetodo();
 * ?>
 *
 * @package      nanoMax
 * @subpackage   MainClass
 * @category     Library-Aggregator
 * @author       Klauss Sant'Ana Guimarães
 * @copyright    Copyright (c) klaussantana.com
 * @license      http://www.gnu.org/licenses/lgpl.html LGPL - LESSER GENERAL PUBLIC LICENSE
 * @link         http://nanoMax.klaussantana.com
 * @version      0.1-dev
 * @filesource   
 **/
class nanoMax
{
	/**
	 * Contém os métodos registrados por nanoMax::Assembly().
	 *
	 * @static
	 * @access   private
	 * @var      array     Instancias de ReflectionMethod das bibliotecas agregadas.
	 **/
	static private $RegisteredMethods = array();
	
	/**
	 * Contém as propriedades registrados por nanoMax::Assembly().
	 *
	 * @static
	 * @access   private
	 * @var      array     Instancias de ReflectionProperty das bibliotecas agregadas.
	 **/
	static private $RegisteredProperties = array();
	
	/**
	 * Contém as instâncias de objetos registrados por nanoMax::Assembly().
	 *
	 * @static
	 * @access   private
	 * @var      array     Instancias das bibliotecas agregadas.
	 **/
	static private $RegisteredObjects = array();
	
	/**
	 * Contém ReflectionClass de classes registrados por nanoMax::Assembly().
	 *
	 * @static
	 * @access   private
	 * @var      array     Instancias de ReflectionClass das bibliotecas agregadas.
	 **/
	static private $ReflectionObjects = array();
	
	/**
	 * Contém os registros dos erros disparados através de um trigger_error()
	 * ou um throw Exception().
	 *
	 * @static
	 * @access   private
	 * @var      array     Instancias de ReflectionClass das bibliotecas agregadas.
	 **/
	static private $Trace = array();
	
	/**
	 * Contém a instância Singleton desta classe.
	 *
	 * @static
	 * @access   private
	 * @var      nanoMax   Instancia Singleton da classe nanoMax.
	 **/
	static private $Instance = null;
	
	
	/**
	 * Construtor da classe
	 *
	 * Não é possível instanciar esta classe deliberadamente.
	 *
	 * @access   private
	 * @see      nanoMax::GetInstance()
	 **/
	private
	function __construct()
	{
	}
	
	/**
	 * Clonador da classe
	 *
	 * Não é possível clonar esta classe.
	 *
	 * @access   private
	 * @see      nanoMax::GetInstance()
	 **/
	private
	function __clone()
	{
	}
	
	/**
	 * Autoload de bibliotecas e classes
	 *
	 * Este método tem duas funcionalidades. Caso receba o argumento
	 * $Library, carrega a biblioteca. Caso não receba argumento algum,
	 * carrega todas as bibliotecas especificadas em var/autoload.xml e
	 * também agrega todas as bibliotecas em var/assembly.xml ao nanoMax.
	 *
	 * Este método pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Library    String contendo o nome da biblioteca/classe que será agregada.
	 **/
	static
	private
	function Autoload()
	{
		global $NM_DIR;
		
		/**
		 * Se não for especificado nenhum argumento, carrega automaticamente
		 * todas as bibliotecas especificadas em var/autoload.xml e agrega
		 * automaticamente as especificadas em var/assembly.xml.
		 **/
		if ( func_num_args() <1 )
		{
			$File1  = $NM_DIR->var .DS .'assembly.xml';
			$File2  = $NM_DIR->var .DS .'autoload.xml';
			
			// Agrega bibliotecas de var/assembly.xml.
			if ( is_file($File1) && is_readable($File1) )
			{
				$File = new SimpleXMLElement($File1, null, true);
				
				foreach ( $File as $Key =>$Value )
				{
					if (strtolower($Value[0]) =='yes' || strtolower($Value[0]) =='1' || strtolower($Value[0]) =='true')
					{
						// Agrega a biblioteca
						static::Assembly($Key);
					}
				}
			}
			
			else
			{
				trigger_error("Não foi possível ler um arquivo de inicialização do framework nanoMax: '{$File1}'.", E_USER_ERROR);
				return false;
			}
			
			// Carrega bibliotecas de var/autoload.xml.
			if ( is_file($File2) && is_readable($File2) )
			{
				$File = new SimpleXMLElement($File2, null, true);
				
				foreach ( $File as $Key =>$Value )
				{
					if (strtolower($Value[0]) =='yes' || strtolower($Value[0]) =='1' || strtolower($Value[0]) =='true')
					{
						// Carrega a biblioteca
						static::Autoload($Key);
					}
				}
			}
			
			else
			{
				trigger_error("Não foi possível ler um arquivo de inicialização do framework nanoMax: '{$File2}'.", E_USER_ERROR);
				return false;
			}
			
			return true;
		}
		
		/**
		 * Adquire todos os argumentos passados
		 **/
		$ARGS    = func_get_args();
		
		/**
		 * Adquire o parâmetro $Library
		 **/
		$Class   = array_shift($ARGS);
		$Class   = basename($Class);
		
		/**
		 * Prepara os nomes dos arquivos a serem procurados em
		 * libs e apps/.shared
		 **/
		$Library = $NM_DIR->libs   .DS .$Class .DS .'main.php';
		$Shared  = $NM_DIR->shared .DS .$Class .DS .'main.php';
		
		if ( !is_null($Class) )
		{
			// Carrega a biblioteca
			if ( is_file($Library) && is_readable($Library) )
			{
				require_once $Library;
			}
			
			// Carrega uma biblioteca compartilhada
			else if ( is_file($Shared) && is_readable($Shared) )
			{
				require_once $Shared;
			}
			
			else
			{
				// Oops... Nenhuma classe foi encontrada
				trigger_error("Classe '{$Class}' não existe.", E_USER_NOTICE);
			}
		}
		
		return true;
	}
	
	/**
	 * Agregador de bibliotecas
	 *
	 * Este método é responsável por agregar métodos e propriedades de outras
	 * bibliotecas, expandindo as funcionalidades da classe nanoMax.
	 *
	 * Este método pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Library    String contendo o nome da biblioteca/classe que será agregada.
	 * @param    bool     $Overload   Opcional. TRUE/FALSE para ligar/deslivar sobrecarga de métodos e propriedades já agregados. Por padrão, $Overload está ligado;
	 * @param    mixed    $Arg,...    Opcional. Argumentos a serem passados ao instanciar $Library.
	 **/
	static
	private
	function Assembly()
	{
		/**
		 * Adquire todos os argumentos passados.
		 **/
		$ARGS     = func_get_args();
		
		/**
		 * Adquire o parâmetro $Library.
		 **/
		$Class = array_shift($ARGS);
		$class = strtolower($Class);
		
		/**
		 * Adquire o parâmetro $Overload.
		 **/
		$Overload = array_shift($ARGS);
		
		if ( $Overload === NULL )
		{
			$Overload = true;
		}
		
		/**
		 * Se a classe não existe é disparado um erro.
		 **/
		if ( !class_exists($Class, true) )
		{
			trigger_error("nanoMax::Assembly($Class): Não foi possível carregar uma classe.");
		}
		
		/**
		 * Se a classe existe, então é agregada à nanoMax
		 **/
		else
		{
			// Importa a biblioteca
			static::$ReflectionObjects[$class] = new ReflectionClass($Class);
			static::$RegisteredObjects[$class] = static::$ReflectionObjects[$class]->newInstanceArgs($ARGS);
			
			// Importa as propriedades
			foreach ( static::$ReflectionObjects[$class]->getProperties() as $Property )
			{
				if ( $Overload || !isset(static::$RegisteredProperties[strtolower($Property->name)]) )
				{
					// Agrega somente se não existir ou $Overload for verdadeiro
					static::$RegisteredProperties[strtolower($Property->name)] = $Property;
				}
			}
			
			// Importa os métodos
			foreach ( static::$ReflectionObjects[$class]->getMethods() as $Method )
			{
				if ( $Method->isPublic() )
				{
					if ( $Overload || !isset(static::$RegisteredMethods[strtolower($Method->name)]) )
					{
						// Agrega somente se não existir ou $Overload for verdadeiro
						static::$RegisteredMethods[strtolower($Method->name)] = $Method;
					}
				}
			}
		}
	}
	
	/**
	 * Handler de erros e excessões
	 *
	 * Trata os erros e excessões disparados.
	 *
	 * Obs.: nanoMax não implementa internamente um tratamento de erros e excessões.
	 * Este método deve ser sobrecarregado através da agregação de uma biblioteca com
	 * esta finalidade, pois está presente apenas para evitar disparos de erros e
	 * excessões.
	 *
	 * Este método deve ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    mixed    $Arg,...    Opcional. Argumentos lançados pelos erros ou excessões.
	 **/
	static
	private
	function Trace()
	{
		static::$Trace[] = func_get_args();
		
		return static::$Trace;
	}
	
	/**
	 * Handler de saída
	 *
	 * Trata a pilha de saída (output buffer).
	 *
	 * Obs.: nanoMax não implementa internamente um tratamento de saída. Este
	 * método deve ser sobrecarregado através da agregação de uma classe para
	 * esta finalidade, pois está presente apenas para evitar disparos de erros e
	 * excessões.
	 *
	 * Este método deve ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Buffer   String contendo a pilha de saída não tratada.
	 * @return   string             String contendo a pilha de saída tratada.
	 **/
	static
	private
	function Output( $Buffer )
	{
		return @(string) $Buffer;
	}
	
	/**
	 * Singleton
	 *
	 * Cria o objeto Singleton, caso ainda não tenha sido criado, e o retorna.
	 *
	 * Este método pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Buffer   String contendo a pilha de saída não tratada.
	 * @return   string             String contendo a pilha de saída tratada.
	 **/
	static
	private
	function GetInstance()
	{
		// Cria uma instância do objeto caso não exista
		if ( ! static::$Instance instanceof static )
		{
			static::$Instance = new static;
		}
		
		// Retorna a instância Singleton
		return static::$Instance;
	}
	
	
	
	/**
	 * Método Mágico para invocar métodos estáticos
	 * 
	 * Este método se encarrega de redirecionar uma requisição para
	 * os métodos registrados dos objetos agregados ao nanoMax.
	 *
	 * Este método não pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Method      O método a ser invocado
	 * @param    array    $Arguments   Os argumentos a repassar ao método
	 * @return   mixed                 Retorna o resultado do método
	 **/
	static
	public
	function __callStatic( $Method, $Arguments )
	{
		// Prepara a variável para busca do método
		$method = strtolower($Method);
		
		// Verifica se o método não está registrado
		if ( !isset(self::$RegisteredMethods[$method]) )
		{
			// Dispara um erro se esta classe não possuir o método
			if ( !in_array($Method, get_class_methods(get_called_class())) )
			{
				throw new Exception('Método não existe.', self::ERR_METHOD_DONT_EXISTS);
			}
			
			// Invoca o método caso exista
			else
			{
				return call_user_func_array( array( get_called_class(), $Method ), $Arguments );
			}
		}
		
		// Invoca o método se estiver registrado
		else
		{
			$Method = static::$RegisteredMethods[$method];
			
			// Verifica se o método é estático e o invoca
			if ( $Method->isStatic() )
			{
				return $Method->invokeArgs(null, $Arguments);
			}
			
			// Senão, invoca do objeto registrado
			else
			{
				return $Method->invokeArgs(static::$RegisteredObjects[strtolower($Method->class)], $Arguments);
			}
		}
	}
	
	/**
	 * Método Mágico para invocar métodos
	 * 
	 * Este método se encarrega de redirecionar uma requisição para
	 * os métodos registrados dos objetos agregados ao nanoMax.
	 *
	 * Na realidade é um atalho para __callStatic(), pois esta classe
	 * não possui métodos públicos explícitos.
	 *
	 * Este método não pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Method      O método a ser invocado
	 * @param    array    $Arguments   Os argumentos a repassar ao método
	 * @return   mixed                 Retorna o resultado do método
	 * @see      nanoMax::__getStatic()
	 **/
	public
	function __call( $Method, $Arguments )
	{
		return static::__callStatic( $Method, $Arguments );
	}
	
	/**
	 * Método Mágico para associar valores às propriedades
	 * 
	 * Este método é acionado sempre que o houver tentativa de associar
	 * um valor a uma propriedade inexistente do objeto. Caso não exista
	 * uma propriedade registrada através de Assembly() este método se
	 * encarregará de criar uma.
	 *
	 * Este método não pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Property   A propriedade que receberá $Value
	 * @param    mixed    $Value      Valor a ser repassado para $Property
	 * @see      nanoMax::Assembly()
	 **/
	public
	function __set( $Property, $Value )
	{
		// Prepara a variável para a busca da popriedade
		$property = strtolower($Property);
		
		// Verifica se a propriedade está registrada
		if ( isset( static::$RegisteredProperties[$property] ) && static::$RegisteredProperties[$property] instanceof ReflectionProperty )
		{
			$Property = static::$RegisteredProperties[$property];
			
			// Associa valor a uma propriedade estática
			if ( $Property->isStatic() )
			{
				$Property->getDeclaringClass()->setStaticPropertyValue($Property->getName(), $Value);
			}
			
			// Associa valor a uma propriedade não estática
			else
			{
				$Property->setValue($Value);
			}
		}
		
		// Cria a propriedade se não estiver registrada
		else
		{
			static::$RegisteredProperties[$property] = $Value;
		}
	}
	
	/**
	 * Método Mágico para adquirir valores às propriedades
	 * 
	 * Este método é acionado sempre que o houver tentativa de chamada a
	 * um valor de uma propriedade inexistente do objeto. Caso não exista
	 * uma propriedade registrada através de Assembly() este método lançará
	 * um erro e retornará nulo.
	 *
	 * Obs.: O erro lançado será tratado pelo método Trace()
	 *
	 * Este método não pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Property   A propriedade que receberá $Value
	 * @return   mixed|null           Retorna o valor da propriedade ou nulo em caso de falha
	 * @see      nanoMax::Assembly()
	 **/
	public
	function __get( $Property )
	{
		// Prepara a variável para a busca da propriedade
		$property = strtolower($Property);
		
		// Verifica se a propriedade está registrada
		if ( isset( static::$RegisteredProperties[$property] ) )
		{
			// Verifica se é uma propriedade agregada e a retorna
			if ( static::$RegisteredProperties[$property] instanceof ReflectionProperty )
			{
				return static::$RegisteredProperties[$property]->getValue(static::$RegisteredObjects[strtolower(static::$RegisteredProperties[$property]->class)]);
			}
			
			// Retorna a propriedade criada manualmente
			else
			{
				return static::$RegisteredProperties[$property];
			}
		}
		
		// Retorna nulo caso não exista a propriedade
		else
		{
			// Oops! Não existe a propriedade
			trigger_error("nanoMax tentou adquirir uma propriedade inexistente: '{$Property}'. Retorno nulo.", E_USER_NOTICE);
			return null;
		}
	}
	
	/**
	 * Método para associar valores às propriedades
	 * 
	 * Este método é um atalho para __set().
	 *
	 * Este método pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Property   A propriedade que receberá $Value
	 * @param    mixed    $Value      Valor a ser repassado para $Property
	 * @see      nanoMax::__set()
	 **/
	static
	private
	function SetProperty( $Property, $Value )
	{
		return static::GetInstance()->__set( $Property, $Value );
	}
	
	/**
	 * Método Mágico para adquirir valores às propriedades
	 * 
	 * Este método é um atalho para __get().
	 *
	 * Este método pode ser sobrecarregado.
	 *
	 * @static
	 * @access   public
	 * @param    string   $Property   A propriedade que receberá $Value
	 * @return   mixed|null           Retorna o valor da propriedade ou nulo em caso de falha
	 * @see      nanoMax::Assembly()
	 **/
	static
	private
	function GetProperty( $Property )
	{
		return static::GetInstance()->__get( $Property );
	}
}

?>