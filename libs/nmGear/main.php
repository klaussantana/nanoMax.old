<?php
/**
 * nmGear (biblioteca)
 *
 * Uma base para as bibliotecas do framework nanoMax.
 *
 * Uma classe abstrata que tem o objetivo de extender outras
 * bibliotecas desenvolvidas para o framework nanoMax disponibilizando
 * recursos fundamentais como: carregar arquivos de configuração,
 * linguagem, preferências e permissões.
 * 
 * Exemplo:
 * ========
 * <?php
 *    class MeuDAO extends nmGear
 *    {
 *       // ... implementar funcionalidades de MeuDAO ... //
 *    }
 *    
 *    MeuDAO::Configuration()->Host        = 'localhost';
 *    MeuDAO::Configuration()->Username    = 'teste';
 *    MeuDAO::Configuration()->Password    = '123456';
 *    MeuDAO::Configuration()->Database    = 'db_teste';
 *    MeuDAO::Configuration()->TablePrefix = 'tb_'
 *    
 *    if ( ! MeuDAO::Connection() )
 *    {
 *       // Neste exemplo, esta Exception será capturada por nanoMax::Trace()
 *       throw new Exception(MeuDAO::Language()->CantConnect);
 *    }
 *    
 *    if ( MeuDAO::Permissions()->Write )
 *    {
 *       // Grava os dados recebidos por formulário na tabela 'db_teste'
 *       $DAO_Result = MeuDAO::Insert($_POST, 'users');
 *    }
 *    
 *    else
 *    {
 *       throw new Exception(MeuDAO::Language()->CantWrite);
 *    }
 *    
 *    echo " O registro foi gravado no banco de dados. O valor do índice primário é {$DAO_Result->PrimaryKey()}.";
 * ?>
 * 
 * NOTA: Os métodos de MeuDAO são exemplos, e não foram especificados neste
 * exemplo, mas considere as seguintes funcionalidades:
 *   - MeuDAO::Connection() retorna a conexão ao banco de dados ou 'false'
 *     em caso de falhas.
 *   - MeuDAO::Insert() recebe uma array associativa e inclui os valores na
 *     tabela especificada no segundo argumento, retornando um objeto MeuDAO
 *     contendo novas configurações para o novo registro gerado.
 *   
 * @package      nanoMax
 * @subpackage   nmGear
 * @category     Library-Blueprint
 * @author       Klauss Sant'Ana Guimarães
 * @copyright    Copyright (c) klaussantana.com
 * @license      http://www.gnu.org/licenses/lgpl.html LGPL - LESSER GENERAL PUBLIC LICENSE
 * @link         http://nanoMax.klaussantana.com
 * @version      0.1-dev
 * @filesource   
 **/
abstract class nmGear
{
	/**
	 * Contém os contextos de configurações globais da biblioteca.
	 *
	 * @static
	 * @access   protected
	 * @var      array       Instâncias de SimpleXMLElement contendo as configurações dos contextos.
	 **/
	static
	protected
	$Configuration = array();
	
	/**
	 * Contém os contextos de configurações da instância da biblioteca.
	 *
	 * @access   protected
	 * @var      array       Clone e static::$Configuration, para personalizar as configurações por instância.
	 **/
	protected
	$InstanceConfiguration = array();
	
	/**
	 * Contém os contextos de linguagens globais do objeto.
	 *
	 * @static
	 * @access   protected
	 * @var      array       Instâncias de SimpleXMLElement contendo as linguagens dos contextos.
	 **/
	static
	protected
	$Language = null;
	
	/**
	 * Contém os contextos de permissões do objeto, seguindo a hierarquia
	 * de Padrão -> Visitantes -> Grupo -> Usuário.
	 *
	 * @static
	 * @access   protected
	 * @var      array       Instâncias de SimpleXMLElement contendo as permissões dos contextos.
	 **/
	static
	protected
	$Permissions = null;
	
	/**
	 * Contém os contextos de preferências do objeto por usuário.
	 *
	 * @static
	 * @access   protected
	 * @var      array       Instâncias de SimpleXMLElement contendo as permissões dos contextos.
	 * @todo     a implementar os métodos que realizam o trabalho
	 **/
	static
	protected
	$Preferences = null;
	
	/**
	 * Adquire as configurações globais da biblioteca
	 *
	 * Este método carrega as configurações globais da biblioteca, padrões
	 * ou sob um contexto.
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear {}
	 *     
	 *     $cfg_sv1 = Database::Configuration('Server_1'); // -nanoMax-/var/configurations/Database/Server_1.xml
	 *     $cfg_sv2 = Database::Configuration('Server_2'); // -nanoMax-/var/configurations/Database/Server_2.xml
	 *     $cfg_sv2 = Database::Configuration('Server_3'); // -nanoMax-/var/configurations/Database/Server_3.xml
	 *     $cfg_def = Database::Configuration();           // -nanoMax-/var/configurations/Database/default.xml
	 * ?>
	 *
	 * NOTA: Se um arquivo de configuração não puder ser encontrado ou lido
	 * será disparado um erro.
	 *
	 * @param  string  $Context    (Opcional) Contexto de configurações a serem retornadas. Valor padrão: default.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as configurações do contexto
	 **/
	static
	public
	function Configuration ( $Context ='default' )
	{
		// A lista dos caminhos absolutos das pastas do framework
		global $NM_DIR;
		
		$class   = strtolower(get_called_class());
		$context = strtolower($Context);
		
		if ( !isset(static::$Configuration) )
		{
			static::$Configuration = array();
		}
		
		// Verifica se ainda não foi carregada a configuração da biblioteca
		if ( !isset( static::$Configuration[$context] ) || !(static::$Configuration[$context] instanceof SimpleXMLElement) )
		{
			// O caminho completo para o arquivo de configuração (DS = DIRECTORY_SEPARATOR)
			$File = $NM_DIR->var .DS .'configurations' .DS .get_called_class() .DS .$Context .'.xml';
			
			// Se a pasta de configurações não existe, cria
			if ( !is_dir($NM_DIR->var .DS .'configurations') )
			{
				mkdir($NM_DIR->var .DS .'configurations', 0744);
			}
			
			// Verifica a existência do arquivo de configuração
			if ( is_dir(realpath(dirname($File))) && is_file($File) && is_readable($File) )
			{
				try
				{
					static::$Configuration[$context] = new SimpleXMLElement($File, null, true);
				}
				
				catch ( Exception $Exception )
				{
					trigger_error(get_called_class() .': Houve um erro ao carregar o arquivo de configuração: ' .$Exception->getMessage());
				}
			}
			
			else
			{
				// Não existe um arquivo de configuração, então captura os valores padrão
				static::$Configuration[$context] = static::DefaultConfiguration($Context);
			}
		}
		
		return static::$Configuration[$context];
	}
	
	/**
	 * Adquire as configurações da instância da biblioteca
	 *
	 * Este método carrega as configurações da instância da biblioteca, padrões
	 * ou sob um contexto.
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear {}
	 *     
	 *     // Configuração padrão do servidor
	 *     Database::Configuration()->Host     = 'localhost';
	 *     Database::Configuration()->Username = 'root';
	 *     Database::Configuration()->Password = '';
	 *     
	 *     // Configuração do servidor 1
	 *     $SV1 = new Database;
	 *     $SV1->InstanceConfiguration()->Host     = 'mysql.mydomain.com';
	 *     $SV1->InstanceConfiguration()->Username = 'my_server_1';
	 *     $SV1->InstanceConfiguration()->Password = '123456';
	 *     
	 *     // Configuração do servidor 2 (contingência)
	 *     $SV2 = new Database;
	 *     $SV2->InstanceConfiguration()->Host     = 'mysql.mysqlhost.com';
	 *     $SV2->InstanceConfiguration()->Username = 'server_2_username';
	 *     $SV2->InstanceConfiguration()->Password = 'xd74Hi10t0';
	 *     
	 *     if ( $SV1->InstanceConfiguration()->Host != Database::Configuration()->Host )
	 *     {
	 *        echo "O servidor 1 está em local diferente do servidor padrão.";
	 *     }
	 *
	 *     if ( $SV2->InstanceConfiguration()->Host != Database::Configuration()->Host )
	 *     {
	 *        echo "O servidor 2 está em local diferente do servidor padrão.";
	 *     }
	 *
	 *     if ( $SV1->InstanceConfiguration()->Host != $SV2->InstanceConfiguration()->Host )
	 *     {
	 *        echo "O servidor 1 está em local diferente do servidor 2.";
	 *     }
	 * ?>
	 *
	 * NOTA: Se um arquivo de configuração não puder ser encontrado ou lido
	 * será disparado um erro.
	 *
	 * @param  string  $Context    (Opcional) Contexto de configurações a serem retornadas. Valor padrão: default.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as configurações do contexto
	 **/
	public
	function InstanceConfiguration ( $Context ='default' )
	{
		// Prepara a variável para ser buscada na matriz
		$context = strtolower($Context);
		
		// Cria a matriz para as configurações da instância
		if ( !isset($this->InstanceConfiguration) || empty($this->InstanceConfiguration) )
		{
			$this->InstanceConfiguration = array();
		}
		
		// Captura as configurações de um contexto se já não tiverem sido capturadas
		if ( !isset($this->InstanceConfiguration[$context]) )
		{
			if ( static::Configuration($context) )
			{
				$this->InstanceConfiguration[$context] = clone static::Configuration($context);
			}
			
			else
			{
				trigger_error(get_called_class() .": Um contexto de configuração não foi localizado. Foi criado um contexto em branco para '{$Context}'.");
				$this->InstanceConfiguration[$context] = new SimpleXMLElement('<Configuration><empty></empty></Configuration>');
			}
		}
		
		if ( $this->InstanceConfiguration[$context] )
		{
			return $this->InstanceConfiguration[$context];
		}
		
		else
		{
			// Dispara um erro pois não localizou o contexto da configuração.
			trigger_error(get_called_class() .": Não foi possível adquirir um contexto de configuração: '{$Context}'.");
			return false;
		}
	}
	
	/**
	 * Adquire as configurações padrões da biblioteca
	 *
	 * Este método carrega as configurações padrões da biblioteca.
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear
	 *     {
	 *        public function DefaultConfiguration( $Context ='default' )
	 *        {
	 *           $DefaultConfiguration  = array();
	 *           
	 *           // As configurações padrão (produção)
	 *           $DefaultConfiguration['default']  = '<Configuration>';
	 *           $DefaultConfiguration['default'] .= '   <Host>localhost</Host>';
	 *           $DefaultConfiguration['default'] .= '   <Username>my_user</Username>';
	 *           $DefaultConfiguration['default'] .= '   <Password>my_password_123456</Password>';
	 *           $DefaultConfiguration['default'] .= '   <Database>production_db</Database>';
	 *           $DefaultConfiguration['default'] .= '   <TablePrefix>tb_prefix_</TablePrefix>';
	 *           $DefaultConfiguration['default'] .= '</Configuration>';
	 *           
	 *           // As configurações do contexto 'dev' (desenvolvimento)
	 *           $DefaultConfiguration['dev']  = '<Configuration>';
	 *           $DefaultConfiguration['dev'] .= '   <Host>localhost</Host>';
	 *           $DefaultConfiguration['dev'] .= '   <Username>root</Username>';
	 *           $DefaultConfiguration['dev'] .= '   <Password></Password>';
	 *           $DefaultConfiguration['dev'] .= '   <Database>teste</Database>';
	 *           $DefaultConfiguration['dev'] .= '   <TablePrefix>tb_</TablePrefix>';
	 *           $DefaultConfiguration['dev'] .= '</Configuration>';
	 *           
	 *           foreach ( $DefaultConfiguration as $iContext =>$iValue )
	 *           {
	 *              $DefaultConfiguration[$iContext]  = new SimpleXMLElement($iValue));
	 *           }
	 *           
	 *           if ( isset($DefaultConfiguration[ strtolower($Context) ]) )
	 *           {
	 *              return $DefaultConfiguration[ strtolower($Context) ];
	 *           }
	 *           
	 *           else
	 *           {
	 *              // Dispara um erro e retorna o contexto padrão
	 *              trigger_error(Database::Language()->CantRetrieveConfigurationContext);
	 *              return $DefaultConfiguration['default'];
    *           }
	 *        }
	 *     }
	 *     
	 *     echo 'Host atual da biblioteca Database: ' . Database::Configuration('dev');
	 * ?>
	 *
	 * NOTA: Este método deve ser sobrecarregado pela classe filha.
	 *
	 * @param  string  $Context    (Opcional) Contexto de configurações a serem retornadas. Valor padrão: default.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as configurações do contexto
	 **/
	static
	public
	function DefaultConfiguration( $Context ='default' )
	{
		$context = strtolower($Context);
		$Configuration = array();
		
		// Definições dos contextos
		$Configuration['default'] = new SimpleXMLElement('<Configuration><empty></empty></Configuration>');;
		
		// Verifica se o contexto escolhido existe
		if ( isset($Configuration[$context]) )
		{
			return $Configuration[$context];
		}
		
		else
		{
			trigger_error(get_called_class() .": O contexto exigido não é padrão e não existe na pasta de configurações: '{$Context}'.");
			return false;
		}
	}
	
	
	/**
	 * Adquire a linguagem global da biblioteca
	 *
	 * Este método carrega as linguagens globais da biblioteca, padrões
	 * ou sob um contexto, levando em consideração o idioma e família do
	 * idioma (ex.: Português do Brasil/pt_br ou Japonês/jp)
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear {}
	 *     
	 *     // Contexto padrão 'default', linguagem 'pt_br'
	 *     echo Database::Language()->ConnectionSuccess;
	 *     
	 *     // Contexto 'errors', linugagem 'jp'
	 *     echo Database::Language('errors','jp')->ConnectionFailure;
	 *     
	 *     // Contexto 'errors', linugagem 'en_us'
	 *     echo Database::Language('errors','en','us')->ConnectionFailure;
	 * ?>
	 *
	 * NOTA: Se um arquivo de linguagem não puder ser encontrado ou lido
	 * será disparado um erro.
	 *
	 * @param  string  $Context    (Opcional) Contexto de linguagem a ser retornado. Valor padrão: default.
	 * @param  string  $Language   (Opcional) Linuagem a carregar. Valor padrão: pt.
	 * @param  string  $Family     (Opcional) Família da linguagem a carregar. Valor padrão: br.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as linugagens do contexto
	 **/
	static
	public
	function Language ( $Context ='default', $Language ='pt', $Family ='br' )
	{
		// A lista dos caminhos absolutos das pastas do framework
		global $NM_DIR;
		
		$class    = strtolower(get_called_class());
		$context  = strtolower($Context);
		$Language = strtolower($Language);
		$Family   = strtolower($Family);
		
		// Define o código do idioma ($LanguageCode)
		if ( !empty( $Family ) )
		{
			$LanguageCode = "{$Language}_{$Family}";
		}
		
		else
		{
			$LanguageCode = $Language;
		}
		
		if ( !isset(static::$Language) )
		{
			static::$Language = array();
		}
		
		if ( !isset(static::$Language[$LanguageCode]) )
		{
			static::$Language[$LanguageCode] = array();
		}
		
		// Verifica se ainda não foi carregada a linguagem da biblioteca
		if ( !isset( static::$Language[$LanguageCode][$context] ) || !(static::$Language[$LanguageCode][$context] instanceof SimpleXMLElement) )
		{
			// O caminho completo para o arquivo de linguagem (DS = DIRECTORY_SEPARATOR)
			$File = $NM_DIR->var .DS .'languages' .DS .get_called_class() .DS .$LanguageCode .DS .$Context .'.xml';
			
			// Se a pasta de linguagens não existe, cria
			if ( !is_dir($NM_DIR->var .DS .'languages') )
			{
				mkdir($NM_DIR->var .DS .'languages', 0744);
			}
			
			// Verifica a existência do arquivo de linguagem
			if ( is_dir(realpath(dirname($File))) && is_file($File) && is_readable($File) )
			{
				try
				{
					static::$Language[$LanguageCode][$context] = new SimpleXMLElement($File, null, true);
				}
				
				catch ( Exception $Exception )
				{
					trigger_error(get_called_class() .': Houve um erro ao carregar o arquivo de linguagem: ' .$Exception->getMessage());
				}
			}
			
			else
			{
				// Não existe um arquivo de configuração, então captura os valores padrão
				static::$Language[$LanguageCode][$context] = static::DefaultLanguage($Context, $Language, $Family);
			}
		}
		
		return static::$Language[$LanguageCode][$context];
	}
	
	/**
	 * Adquire as linguagens padrões da biblioteca
	 *
	 * Este método retorna as linguagens padrões da biblioteca.
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear
	 *     {
	 *        public function DefaultLanguage( $Context ='default', $Language ='pt', $Family ='br' )
	 *        {
	 *           $DefaultLanguages = array();
	 *           
	 *           if ( !empty($Family) )
	 *           {
	 *              $Language_Code = strtolower("{$Language}_{$Family}");
	 *           }
	 *           
	 *           else
	 *           {
	 *              $Language_Code = strtolower($Language);
	 *           }
	 *           
	 *           // As linguagens que serão utilizadas (pt_br)
	 *           $DefaultLanguages['pt_br']['default']  = '<Language>';
	 *           $DefaultLanguages['pt_br']['default'] .= '   <Errors>';
	 *           $DefaultLanguages['pt_br']['default'] .= '      <CantConnect>Não foi possível conectar ao banco de dados.</CantConnect>';
	 *           $DefaultLanguages['pt_br']['default'] .= '      <CantCreate>Não foi possível inserir informações no banco de dados.</CantCreate>';
	 *           $DefaultLanguages['pt_br']['default'] .= '      <CantRetrieve>Não foi possível recuperar informações do banco de dados.</CantRetrieve>';
	 *           $DefaultLanguages['pt_br']['default'] .= '      <CantUpdate>Não foi possível atualizar informações no banco de dados.</CantUpdate>';
	 *           $DefaultLanguages['pt_br']['default'] .= '      <CantDelete>Não foi possível apagar informações no banco de dados.</CantDelete>';
	 *           $DefaultLanguages['pt_br']['default'] .= '   </Errors>';
	 *           
	 *           // As linguagens que serão utilizadas (pt_br)
	 *           $DefaultLanguages['en_us']['default']  = '<Language>';
	 *           $DefaultLanguages['en_us']['default'] .= '   <Errors>';
	 *           $DefaultLanguages['en_us']['default'] .= '      <CantConnect>Can not connect to the database.</CantConnect>';
	 *           $DefaultLanguages['en_us']['default'] .= '      <CantCreate>Can not insert values into database.</CantCreate>';
	 *           $DefaultLanguages['en_us']['default'] .= '      <CantRetrieve>Can not read values from database.</CantRetrieve>';
	 *           $DefaultLanguages['en_us']['default'] .= '      <CantUpdate>Can not update values in database.</CantUpdate>';
	 *           $DefaultLanguages['en_us']['default'] .= '      <CantDelete>Can not erase registers from database.</CantDelete>';
	 *           $DefaultLanguages['en_us']['default'] .= '   </Errors>';
	 *           
	 *           foreach ( $DefaultLanguages as $iLanguage =>$iContent )
	 *           {
	 *              foreach ( $iContent as $iContext =>$iValue )
	 *              {
	 *                 $DefaultLanguages[$iLanguage][$iContext]  = new SimpleXMLElement($iValue));
	 *              }
	 *           }
	 *           
	 *           if ( isset($DefaultLanguages[$LanguageCode][$Context]) )
	 *           {
	 *              return $DefaultLanguages[$LanguageCode][$Context];
	 *           }
	 *           
	 *           else
	 *           {
	 *              if ( isset($DefaultLanguages[$LanguageCode]) )
	 *              {
	 *                 trigger_error("A linguagem `{$LanguageCode}` não possúi o contexto `{$Context}`. Utilizado `default` por padrão.");
	 *                 
	 *                 return $DefaultLanguages[$LanguageCode]['default'];
	 *              }
	 *              
	 *              else
	 *              {
	 *                 if ( isset($DefaultLanguages['pt_br'][$Context]) )
	 *                 {
	 *                    trigger_error("Não foi possível carregar a linguagem `{$LanguageCode}`. Utilizado `pt_br` por padrão.");
	 *                    
	 *                    return $DefaultLanguages['pt_br'][$Context];
	 *                 }
	 *                 
	 *                 else
	 *                 {
	 *                    trigger_error("Não foi possível carregar a linguagem `{$LanguageCode}`, tampouco o contexto `{$Context}`. Utilizado `pt_br` e `default` por padrão.");
	 *                    
	 *                    return $DefaultLanguages['pt_br']['default'];
	 *                 }
    *           }
	 *        }
	 *        
	 *        // ... demais definições dos métodos de `Database` ... //
	 *     }
	 *     
	 *     // ... alguma programação estrutural ... //
	 *     
	 *     if ( ! Database::Connect() )
	 *     {
	 *        trigger_error( Database::DefaultLanguage()->CantConnect )
	 *     }
	 * ?>
	 *
	 * NOTA: Este método deve ser sobrecarregado pela classe filha.
	 *
	 * @param  string  $Context    (Opcional) Contexto de linguagens a serem retornadas. Valor padrão: default.
	 * @param  string  $Language   (Opcional) A linguagem a ser utilizada. Valor padrão: pt.
	 * @param  string  $Family     (Opcional) A família da linguagem a ser utilizada. Valor padrão: br.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as linguagens do contexto
	 **/
	static
	public
	function DefaultLanguage( $Context ='default', $Language ='pt', $Family ='br' )
	{
		$context  = strtolower($Context);
		$Language = strtolower($Language);
		$Family   = strtolower($Family);
		
		// Define o código do idioma ($LanguageCode)
		if ( !empty( $Family ) )
		{
			$LanguageCode = "{$Language}_{$Family}";
		}
		
		else
		{
			$LanguageCode = $Language;
		}
		
		$DefaultLanguage = array();
		
		// Definições dos contextos
		$DefaultLanguage['default'] = array();
		
		// Verifica se o contexto, linguagem e família escolhidos existem
		if ( isset($DefaultLanguage[$context][$LanguageCode]) )
		{
			return $DefaultLanguage[$context][$LanguageCode];
		}
		
		else
		{
			trigger_error(get_called_class() .": O contexto exigido não é padrão e não existe na pasta de linguagens: '{$Context}'.");
			return new SimpleXMLElement("<Language><empty></empty></Language>");
		}
	}
	
	/**
	 * Adquire as permissões de utilização da biblioteca
	 *
	 * Este método carrega as permissões de utilização da biblioteca, 
	 * começando pelo nível de visitante, sobrescrevendo pelas permissões
	 * de nível de grupo do usuário e então sobrescrevendo pelas
	 * permissões individuais do usuário.
	 *
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear {}
	 *     
	 *     if ( ! Database::Permissions()->Update )
	 *     {
	 *        echo 'Você não tem permissão para modificar o banco de dados.';
	 *     }
	 * ?>
	 *
	 * NOTA: Se uma permissão não foi especificada em um arquivo ou pelo
	 * método `DefaultPermissions()` então, no exemplo acima, seria
	 * retornado um valor `null` (vazio), que equivale a `false` (falso).
	 *
	 * NOTA: Se um arquivo de permissão não puder ser encontrado ou lido
	 * será disparado um erro.
	 *
	 * @param  string  $Context    (Opcional) Contexto de permissões a ser retornado. Valor padrão: default.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as permissões do usuário para determinado contexto.
	 **/
	static
	public
	function Permissions ( $Context ='default' )
	{
		global $NM_DIR,     // Os caminhos das pastas do framework
		       $NM_PROFILE, // O código do perfil do usuário
				 $NM_GROUP;   // O código do grupo do usuário
		
		$class   = strtolower(get_called_class());
		$context = strtolower($Context);
		
		if ( empty($NM_PROFILE) )
		{
			$Profile = 'VISITOR';
		}
		
		else
		{
			$Profile = (string) $NM_PROFILE;
		}
		
		if ( empty($NM_GROUP) )
		{
			$Group = 'VISITORS';
		}
		
		else
		{
			// $NM_GROUP pode ser um objeto, 
			$Group = $NM_GROUP;
		}
		
		if ( !isset(static::$Permissions) )
		{
			static::$Permissions = array();
		}
		
		// Verifica se ainda não foi carregada a configuração da biblioteca
		if ( !isset( static::$Permissions[$context] ) || !(static::$Permissions[$context] instanceof SimpleXMLElement) )
		{
			// Verifica a existência da pasta de permissões (DS = DIRECTORY_SEPARATOR)
			if ( !is_dir($NM_DIR->var .DS .'permissions') )
			{
				mkdir($NM_DIR->var .DS .'permissions', 0744);
			}
			
			// A pasta onde serão procuradas as permissões
			$PermissionsDirectory = $NM_DIR->var .DS .'permissions' .DS .get_called_class();
			
			// As permissões padrão da classe (gerais)
			if ( is_file($DefaultPermissions = $PermissionsDirectory .DS ."{$Context}.xml") && is_readable($DefaultPermissions) )
			{
				$DefaultPermissions = new SimpleXMLElement($DefaultPermissions, null, true);
			}
			
			else
			{
				$DefaultPermissions = static::DefaultPermissions($Context);
			}
			
			// As permissões dos grupos
			if ( is_string($Group) && is_file($GroupPermissions = $PermissionsDirectory .DS ."{$Context}.G_{$Group}.xml") && is_readable($GroupPermissions) )
			{
				$GroupPermissions = new SimpleXMLElement($GroupPermissions, null, true);
			}
			
			else
			{
				// Verifica se o grupo é filho de algum outro grupo
				if ( is_object($Group) && is_callable(array($Group, 'Parent')) && $Group->Parent() )
				{
					$GroupPermissions    = array();
					$NewGroupPermissions = new SimpleXMLElement('<Permissions><empty></empty></Permissions>');
					
					// Percorre os grupos e seus pais
					do
					{
						if ( is_file($GroupPermissionsFile = $PermissionsDirectory .DS ."{$Context}.G_{$Group}.xml") && is_readable($GroupPermissionsFile) )
						{
							$GroupPermissions[] = new SimpleXMLElement($GroupPermissionsFile, null, true);
						}
					} while ( $Group = $Group->Parent() );
					
					// Inverte a ordem dos pais/filhos.
					// Priridade: Filho sobrescreve Pai
					$GroupPermissions = array_reverse($GroupPermissions);
					
					// Sobrescreve os valores iguais num novo elemento
					foreach( $GroupPermissions as $Perms )
					{
						foreach ( $Perms as $Key =>$Value )
						{
							$NewGroupPermissions->{$Key} = $Value;
						}
					}
					
					$GroupPermissions = $NewGroupPermissions;
					unset($NewGroupPermissions, $GroupPermissionsFile, $Key, $Value, $Perms);
				}
				
				// Não é filho de nenhum outro grupo ou não tem suporte
				else
				{
					if ( is_file($GroupPermissions = $PermissionsDirectory .DS ."{$Context}.G_{$Group}.xml") && is_readable($GroupPermissions) )
					{
						$GroupPermissions = new SimpleXMLElement($GroupPermissions, null, true);
					}
					
					else
					{
						$GroupPermissions = new SimpleXMLElement('<Permissions><empty></empty></Permissions>');
					}
				}
			}
			
			// As permissões dos usuários (adquire do perfil do usuário)
			if ( is_file($ProfilePermissions = $PermissionsDirectory .DS ."{$Context}.P_{$Profile}.xml") && is_readable($ProfilePermissions) )
			{
				$ProfilePermissions = new SimpleXMLElement($ProfilePermissions, null, true);
			}
			
			else
			{
				$ProfilePermissions = new SimpleXMLElement('<Permissions><empty></empty></Permissions>');
			}
			
			$Permissions = new SimpleXMLElement('<Permissions><empty></empty></Permissions>');
			
			// Sobrescreve as permissões com as permissões padrões
			foreach ( $DefaultPermissions as $Key =>$Value )
			{
				$Value = strtolower($Value);
				$Value = ($Value == 'yes') || ($Value =='true') || ($Value =='1');
				$Permissions->{$Key} = $Value;
			}
			
			// Sobrescreve as permissões padrões com as permissões do grupo do usuário
			foreach ( $GroupPermissions as $Key =>$Value )
			{
				$Value = strtolower($Value);
				$Value = ($Value == 'yes') || ($Value =='true') || ($Value =='1');
				$Permissions->{$Key} = $Value;
			}
			
			// Sobrescreve as permissões com as permissões específicas do usuário (pelo perfil)
			foreach ( $ProfilePermissions as $Key =>$Value )
			{
				$Value = strtolower($Value);
				$Value = ($Value == 'yes') || ($Value =='true') || ($Value =='1');
				$Permissions->{$Key} = $Value;
			}
			
			static::$Permissions[$context] = $Permissions;
		}
		
		return static::$Permissions[$context];
	}
	
	/**
	 * Adquire as permissões padrões de utilização da biblioteca
	 *
	 * Este método deve ser sobrecarregado pela classe filha, e
	 * deve retornar um objeto `SimpleXMLElement` contendo as
	 * permissões de uso da biblioteca mais básicas como, por
	 * exemplo, as permissões que um visitante teria ao acessar
	 * o programa.
	 * 
	 * Exemplo:
	 * <?php
	 *     class Database extends nmGear
	 *     {
	 *        public function DefaultPermissions( $Context ='default' )
	 *        {
	 *           $DefaultPermissions = array();
	 *           
	 *           // O contexto padrão
	 *           $DefaultPermissions['default']  = '<Permissions>';
	 *           $DefaultPermissions['default'] .= '   <Create>No</Create>';
	 *           $DefaultPermissions['default'] .= '   <Retrieve>Yes</Retrieve>';
	 *           $DefaultPermissions['default'] .= '   <Update>No</Update>';
	 *           $DefaultPermissions['default'] .= '   <Delete>No</Delete>';
	 *           $DefaultPermissions['default'] .= '</Permissions>';
	 *           
	 *           // O contexto utilizado para logar
	 *           $DefaultPermissions['log']  = '<Permissions>';
	 *           $DefaultPermissions['log'] .= '   <Create>Yes</Create>';
	 *           $DefaultPermissions['log'] .= '   <Retrieve>Yes</Retrieve>';
	 *           $DefaultPermissions['log'] .= '   <Update>No</Update>';
	 *           $DefaultPermissions['log'] .= '   <Delete>No</Delete>';
	 *           $DefaultPermissions['log'] .= '</Permissions>';
	 *           
	 *           foreach ( $DefaultPermissions as $iContext =>$iValue )
	 *           {
	 *              $DefaultPermissions[$iContext]  = new SimpleXMLElement($iValue));
	 *           }
	 *           
	 *           if ( isset($DefaultPermissions[ strtolower($Context) ]) )
	 *           {
	 *              return $DefaultPermissions[ strtolower($Context) ];
	 *           }
	 *           
	 *           else
	 *           {
	 *              // Dispara um erro e retorna o contexto padrão
	 *              trigger_error(Database::Language()->CantRetrievePermissionsContext);
	 *              return $DefaultPermissions['default'];
    *           }
	 *        }
	 *     }
	 *     
	 *     // Nota: Se não existir arquivo contendo as permissões,
	 *     // automaticamente serão utilizadas as permissões do
	 *     // método `DefaultPermissions()`.
	 *     if ( ! Database::Permissions()->Update )
	 *     {
	 *        echo 'Você não tem permissão para modificar o banco de dados.';
	 *     }
	 * ?>
	 *
	 * NOTA: Se uma permissão não foi especificada em um arquivo ou pelo
	 * método `DefaultPermissions()` então, no exemplo acima, seria
	 * retornado um valor `null` (vazio), que equivale a `false` (falso).
	 *
	 * @param  string  $Context    (Opcional) Contexto de permissões a ser retornado. Valor padrão: default.
	 * @return SimpleXMLElement    Um objeto SimpleXMLElement com as permissões do usuário para determinado contexto.
	 **/
	static
	public
	function DefaultPermissions( $Context ='default' )
	{
		$context = strtolower($Context);
		$Permissions = array();
		
		// Definições dos contextos
		$Permissions['default'] = new SimpleXMLElement('<Permissions><empty></empty></Permissions>');;
		
		// Verifica se o contexto escolhido existe
		if ( isset($Permissions[$context]) )
		{
			return $Permissions[$context];
		}
		
		else
		{
			trigger_error(get_called_class() .": O contexto exigido não é padrão e não existe na pasta de permissões: '{$Context}'.");
			return false;
		}
	}
	
	
}

?>