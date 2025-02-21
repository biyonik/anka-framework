<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AspectRegistryInterface;

/**
 * ProxyFactory sınıfı.
 *
 * Aspect'leri uygulayacak proxy sınıfları oluşturan fabrika.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ProxyFactory
{
    /**
     * Aspect registry.
     *
     * @var AspectRegistryInterface
     */
    protected AspectRegistryInterface $registry;

    /**
     * Method invoker.
     *
     * @var MethodInvoker
     */
    protected MethodInvoker $invoker;

    /**
     * Önbelleğe alınmış proxy sınıflar.
     *
     * @var array<string, string>
     */
    protected array $proxyClassCache = [];

    /**
     * Önbellek dizini.
     *
     * @var string|null
     */
    protected ?string $cacheDir = null;

    /**
     * Constructor.
     *
     * @param AspectRegistryInterface $registry Aspect registry
     * @param string|null $cacheDir Önbellek dizini
     */
    public function __construct(AspectRegistryInterface $registry, ?string $cacheDir = null)
    {
        $this->registry = $registry;
        $this->invoker = new MethodInvoker($registry);
        $this->cacheDir = $cacheDir;

        if ($this->cacheDir !== null && !is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0755, true)) {
            throw new \RuntimeException(sprintf('Önbellek dizini oluşturulamadı: %s', $this->cacheDir));
        }
    }

    /**
     * Bir sınıfın proxy'sini oluşturur.
     *
     * @param string $className Proxy'si oluşturulacak sınıf adı
     * @return string Proxy sınıf adı
     */
    public function createProxy(string $className): string
    {
        // Önbellekte var mı?
        if (isset($this->proxyClassCache[$className])) {
            return $this->proxyClassCache[$className];
        }

        // Sınıf yansıması oluştur
        try {
            $reflClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(sprintf('Sınıf bulunamadı: %s', $className), 0, $e);
        }

        // Proxy sınıf adı oluştur
        $proxyClassName = $this->generateProxyClassName($className);

        // Proxy sınıf tanımı oluştur
        $proxyClassContent = $this->generateProxyClassContent($reflClass, $proxyClassName);

        // Proxy sınıfı değerlendir
        $this->evaluateProxyClass($proxyClassName, $proxyClassContent);

        // Önbelleğe ekle
        $this->proxyClassCache[$className] = $proxyClassName;

        return $proxyClassName;
    }

    /**
     * Bir sınıfın proxy'sini oluşturur ve örneğini döndürür.
     *
     * @param string $className Proxy'si oluşturulacak sınıf adı
     * @param array $constructorArgs Constructor parametreleri
     * @return object Proxy örneği
     */
    public function createProxyInstance(string $className, array $constructorArgs = []): object
    {
        $proxyClassName = $this->createProxy($className);

        // Proxy örneği oluştur
        try {
            $reflClass = new \ReflectionClass($proxyClassName);

            if (empty($constructorArgs)) {
                return $reflClass->newInstance();
            }

            return $reflClass->newInstanceArgs($constructorArgs);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf('Proxy örneği oluşturulamadı: %s (%s)', $proxyClassName, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Proxy sınıf adı oluşturur.
     *
     * @param string $className Orijinal sınıf adı
     * @return string Proxy sınıf adı
     */
    protected function generateProxyClassName(string $className): string
    {
        // Namespace'i ve sınıf adını ayır
        $pos = strrpos($className, '\\');

        if ($pos !== false) {
            $namespace = substr($className, 0, $pos);
            $shortClassName = substr($className, $pos + 1);
        } else {
            $namespace = '';
            $shortClassName = $className;
        }

        // Proxy sınıf adı oluştur
        $proxyShortClassName = $shortClassName . 'Proxy_' . md5($className . microtime(true));

        if ($namespace) {
            return $namespace . '\\' . $proxyShortClassName;
        }

        return $proxyShortClassName;
    }

    /**
     * Proxy sınıf içeriği oluşturur.
     *
     * @param \ReflectionClass $reflClass Orijinal sınıf yansıması
     * @param string $proxyClassName Proxy sınıf adı
     * @return string Proxy sınıf içeriği
     */
    protected function generateProxyClassContent(\ReflectionClass $reflClass, string $proxyClassName): string
    {
        $className = $reflClass->getName();

        // Namespace'i ve sınıf adını ayır
        $pos = strrpos($proxyClassName, '\\');

        if ($pos !== false) {
            $namespace = substr($proxyClassName, 0, $pos);
            $shortProxyClassName = substr($proxyClassName, $pos + 1);
        } else {
            $namespace = '';
            $shortProxyClassName = $proxyClassName;
        }

        // Namespace tanımı
        $namespaceCode = $namespace ? "namespace $namespace;\n\n" : '';

        // Sınıf tanımı başlat
        $content = "<?php\n\n";
        $content .= $namespaceCode;
        $content .= "/**\n";
        $content .= " * Automatically generated proxy class for $className.\n";
        $content .= " * @generated\n";
        $content .= " */\n";

        if ($reflClass->isInterface()) {
            $content .= "class $shortProxyClassName implements \\$className\n";
        } else {
            $content .= "class $shortProxyClassName extends \\$className\n";
        }

        $content .= "{\n";

        // Method invoker özelliği ekle
        $content .= "    /**\n";
        $content .= "     * @var \\Framework\\Core\\Aspects\\MethodInvoker\n";
        $content .= "     */\n";
        $content .= "    private \$__methodInvoker;\n\n";

        // Constructor ekle
        $content .= "    /**\n";
        $content .= "     * Constructor.\n";
        $content .= "     */\n";
        $content .= "    public function __construct()\n";
        $content .= "    {\n";
        $content .= "        \$this->__methodInvoker = new \\Framework\\Core\\Aspects\\MethodInvoker(new \\Framework\\Core\\Aspects\\AspectRegistry());\n";
        $content .= "        parent::__construct(...func_get_args());\n";
        $content .= "    }\n\n";

        // Metodları ekle
        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor() || $method->isStatic()) {
                continue;
            }

            // Metod tanımı
            $parameters = [];
            $parametersWithType = [];
            $parametersCall = [];

            foreach ($method->getParameters() as $param) {
                $paramName = ' . $param->getName()';
                $parameters[] = $paramName;
                
                // Parametre tipi
                $paramType = '';
                if ($param->hasType()) {
                    $type = $param->getType();
                    
                    if ($type instanceof \ReflectionNamedType) {
                        $paramType = ($type->isBuiltin() ? '' : '\\') . $type->getName();
                        
                        if ($type->allowsNull()) {
                            $paramType = '?' . $paramType;
                        }
                        
                        $paramType .= ' ';
                    }
                }
                
                // Varsayılan değer
                $paramDefault = '';
                if ($param->isDefaultValueAvailable()) {
                    $default = $param->getDefaultValue();
                    
                    if (is_string($default)) {
                        $paramDefault = " = '" . addslashes($default) . "'";
                    } elseif (is_bool($default)) {
                        $paramDefault = $default ? ' = true' : ' = false';
                    } elseif (is_null($default)) {
                        $paramDefault = ' = null';
                    } elseif (is_numeric($default)) {
                        $paramDefault = ' = ' . $default;
                    } elseif (is_array($default)) {
                        $paramDefault = ' = []';
                    }
                }
                
                // Referans kontrolü
                if ($param->isPassedByReference()) {
                    $paramName = '&' . $paramName;
                }
                
                // Variadic kontrolü
                if ($param->isVariadic()) {
                    $paramName = '...' . $paramName;
                    $paramType = '';
                }
                
                $parametersWithType[] = $paramType . $paramName . $paramDefault;
                $parametersCall[] = $paramName;
            }
            
            // Dönüş tipi
            $returnType = '';
            if ($method->hasReturnType()) {
                $type = $method->getReturnType();
                
                if ($type instanceof \ReflectionNamedType) {
                    $returnType = ($type->isBuiltin() ? '' : '\\') . $type->getName();
                    
                    if ($type->allowsNull()) {
                        $returnType = '?' . $returnType;
                    }
                    
                    $returnType = ': ' . $returnType;
                }
            }
            
            // Metod tanımı
            $content .= "    /**\n";
            $content .= "     * {@inheritdoc}\n";
            $content .= "     */\n";
            $content .= "    public function " . $method->getName() . "(" . implode(', ', $parametersWithType) . ")" . $returnType . "\n";
            $content .= "    {\n";
            
            // Aspect intercept kodu
            $content .= "        return \$this->__methodInvoker->invoke(\$this, '" . $method->getName() . "', [" . implode(', ', $parameters) . "]);\n";
            
            $content .= "    }\n\n";
        }
        
        // Sınıf tanımını bitir
        $content .= "}\n";
        
        return $content;
    }
    
    /**
     * Proxy sınıfını değerlendirir ve yükler.
     * 
     * @param string $proxyClassName Proxy sınıf adı
     * @param string $proxyClassContent Proxy sınıf içeriği
     * @return void
     */
    protected function evaluateProxyClass(string $proxyClassName, string $proxyClassContent): void
    {
        // Önbelleğe kaydet
        if ($this->cacheDir !== null) {
            $fileName = $this->cacheDir . '/' . str_replace('\\', '_', $proxyClassName) . '.php';
            file_put_contents($fileName, $proxyClassContent);
            require_once $fileName;
            return;
        }
        
        // Direkt olarak değerlendir
        eval(substr($proxyClassContent, 5)); // "<?php" kısmını atla
    }
}