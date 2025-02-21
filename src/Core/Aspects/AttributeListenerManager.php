<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Attributes\After;
use Framework\Core\Aspects\Attributes\AfterReturning;
use Framework\Core\Aspects\Attributes\AfterThrowing;
use Framework\Core\Aspects\Attributes\Around;
use Framework\Core\Aspects\Attributes\Aspect;
use Framework\Core\Aspects\Attributes\Before;
use Framework\Core\Aspects\Attributes\Pointcut;
use Framework\Core\Aspects\Contracts\AspectRegistryInterface;
use Framework\Core\Aspects\Pointcut\AnnotationPointcut;
use Framework\Core\Aspects\Pointcut\MethodPointcut;

/**
 * AttributeListenerManager sınıfı.
 *
 * PHP 8 attribute'larını kullanarak aspect, pointcut ve advice'ları yönetir.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AttributeListenerManager
{
    /**
     * Aspect registry.
     *
     * @var AspectRegistryInterface
     */
    protected AspectRegistryInterface $registry;

    /**
     * Constructor.
     *
     * @param AspectRegistryInterface $registry Aspect registry
     */
    public function __construct(AspectRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Bir aspect sınıfını kaydetmek için tüm attribute'ları tarar.
     *
     * @param string|object $class Aspect sınıfı adı veya örneği
     * @return self Akıcı arayüz için
     */
    public function registerClassListeners(string|object $class): self
    {
        $className = is_string($class) ? $class : get_class($class);
        $instance = is_string($class) ? null : $class;

        try {
            $reflClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(sprintf('Sınıf bulunamadı: %s', $className), 0, $e);
        }

        // Sınıf bir aspect mi?
        $aspectAttr = $reflClass->getAttributes(Aspect::class);

        if (empty($aspectAttr)) {
            // Aspect attribute'u yoksa, sadece belirli bir sınıf için advice'ları ara
            $this->registerClassAdvices($reflClass, $instance);
            return $this;
        }

        // Aspect attribute'u varsa, aspect olarak kaydet
        $aspectAttrInstance = $aspectAttr[0]->newInstance();
        $aspectId = $aspectAttrInstance->id ?? $className;
        $aspectPriority = $aspectAttrInstance->priority;

        // Aspect örneği oluştur (eğer zaten instance verilmemişse)
        if ($instance === null) {
            try {
                $instance = $reflClass->newInstance();
            } catch (\Throwable $e) {
                throw new \RuntimeException(sprintf('Aspect örneği oluşturulamadı: %s', $className), 0, $e);
            }
        }

        // Eğer AbstractAspect'ten türememişse, dinamik bir aspect oluştur
        if (!$instance instanceof AbstractAspect) {
            $aspect = new DynamicAspect($aspectId, $aspectPriority);
        } else {
            $aspect = $instance;
            if ($aspectAttrInstance->id !== null) {
                $aspect->setId($aspectAttrInstance->id);
            }
            $aspect->setPriority($aspectPriority);
        }

        // Pointcut'ları işle
        $this->processPointcuts($reflClass, $aspect);

        // Aspect'i registry'ye kaydet
        $this->registry->register($aspect);

        // Advice'ları işle
        $this->processAdvices($reflClass, $aspect, $instance);

        return $this;
    }

    /**
     * Bir dizindeki tüm aspect sınıflarını tarar ve kaydeder.
     *
     * @param string $directory Taranacak dizin
     * @param string $namespace Sınıf namespace'i
     * @return self Akıcı arayüz için
     */
    public function registerListenersFromDirectory(string $directory, string $namespace = ''): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException(sprintf('Dizin bulunamadı: %s', $directory));
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname(), $namespace);

            if ($className === null) {
                continue;
            }

            // Sınıfın Aspect attribute'u var mı?
            try {
                $reflClass = new \ReflectionClass($className);

                if (!empty($reflClass->getAttributes(Aspect::class))) {
                    $this->registerClassListeners($className);
                }
            } catch (\Throwable $e) {
                // Hata durumunda devam et
                continue;
            }
        }

        return $this;
    }

    /**
     * Sınıf metotlarındaki pointcut attribute'larını işler.
     *
     * @param \ReflectionClass $reflClass Sınıf yansıması
     * @param AbstractAspect $aspect Aspect örneği
     * @return void
     */
    protected function processPointcuts(\ReflectionClass $reflClass, AbstractAspect $aspect): void
    {
        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $pointcutAttrs = $method->getAttributes(Pointcut::class);

            if (empty($pointcutAttrs)) {
                continue;
            }

            foreach ($pointcutAttrs as $attr) {
                $pointcutAttr = $attr->newInstance();
                $pointcutName = $pointcutAttr->name ?? $method->getName();

                // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
                if (str_starts_with($pointcutAttr->expression, '@')) {
                    // Annotation pointcut
                    $attributeName = substr($pointcutAttr->expression, 1);
                    $pointcut = new AnnotationPointcut($attributeName, $pointcutName);
                } else {
                    // Method pointcut
                    $pointcut = new MethodPointcut('', null, $pointcutName);
                    $pointcut->parse($pointcutAttr->expression);
                }

                // Aspect'e pointcut ekle
                $aspect->addPointcut($pointcut);
            }
        }
    }

    /**
     * Sınıf metotlarındaki advice attribute'larını işler.
     *
     * @param \ReflectionClass $reflClass Sınıf yansıması
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Sınıf örneği
     * @return void
     */
    protected function processAdvices(\ReflectionClass $reflClass, AbstractAspect $aspect, object $instance): void
    {
        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Before advice
            $beforeAttrs = $method->getAttributes(Before::class);
            foreach ($beforeAttrs as $attr) {
                $beforeAttr = $attr->newInstance();
                $this->registerBeforeAdvice($aspect, $instance, $method, $beforeAttr);
            }

            // AfterReturning advice
            $afterReturningAttrs = $method->getAttributes(AfterReturning::class);
            foreach ($afterReturningAttrs as $attr) {
                $afterReturningAttr = $attr->newInstance();
                $this->registerAfterReturningAdvice($aspect, $instance, $method, $afterReturningAttr);
            }

            // AfterThrowing advice
            $afterThrowingAttrs = $method->getAttributes(AfterThrowing::class);
            foreach ($afterThrowingAttrs as $attr) {
                $afterThrowingAttr = $attr->newInstance();
                $this->registerAfterThrowingAdvice($aspect, $instance, $method, $afterThrowingAttr);
            }

            // After advice
            $afterAttrs = $method->getAttributes(After::class);
            foreach ($afterAttrs as $attr) {
                $afterAttr = $attr->newInstance();
                $this->registerAfterAdvice($aspect, $instance, $method, $afterAttr);
            }

            // Around advice
            $aroundAttrs = $method->getAttributes(Around::class);
            foreach ($aroundAttrs as $attr) {
                $aroundAttr = $attr->newInstance();
                $this->registerAroundAdvice($aspect, $instance, $method, $aroundAttr);
            }
        }
    }

    /**
     * Before advice kaydeder.
     *
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Advice metodunun bulunduğu örnek
     * @param \ReflectionMethod $method Advice metodu
     * @param Before $attr Before attribute
     * @return void
     */
    protected function registerBeforeAdvice(AbstractAspect $aspect, object $instance, \ReflectionMethod $method, Before $attr): void
    {
        // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
        $pointcut = $this->createPointcutFromExpression($attr->pointcut);

        // Before advice oluştur
        $advice = new Advice\BeforeAdvice($aspect, $pointcut, [$instance, $method->getName()], $attr->priority);

        // Aspect'e advice ekle (DynamicAspect ise)
        if ($aspect instanceof DynamicAspect) {
            $aspect->addAdvice($advice);
        }
    }

    /**
     * AfterReturning advice kaydeder.
     *
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Advice metodunun bulunduğu örnek
     * @param \ReflectionMethod $method Advice metodu
     * @param AfterReturning $attr AfterReturning attribute
     * @return void
     */
    protected function registerAfterReturningAdvice(AbstractAspect $aspect, object $instance, \ReflectionMethod $method, AfterReturning $attr): void
    {
        // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
        $pointcut = $this->createPointcutFromExpression($attr->pointcut);

        // AfterReturning advice oluştur
        $advice = new Advice\AfterReturningAdvice($aspect, $pointcut, [$instance, $method->getName()], $attr->priority);

        // Aspect'e advice ekle (DynamicAspect ise)
        if ($aspect instanceof DynamicAspect) {
            $aspect->addAdvice($advice);
        }
    }

    /**
     * AfterThrowing advice kaydeder.
     *
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Advice metodunun bulunduğu örnek
     * @param \ReflectionMethod $method Advice metodu
     * @param AfterThrowing $attr AfterThrowing attribute
     * @return void
     */
    protected function registerAfterThrowingAdvice(AbstractAspect $aspect, object $instance, \ReflectionMethod $method, AfterThrowing $attr): void
    {
        // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
        $pointcut = $this->createPointcutFromExpression($attr->pointcut);

        // AfterThrowing advice oluştur
        $advice = new Advice\AfterThrowingAdvice($aspect, $pointcut, [$instance, $method->getName()], $attr->priority);

        // Aspect'e advice ekle (DynamicAspect ise)
        if ($aspect instanceof DynamicAspect) {
            $aspect->addAdvice($advice);
        }
    }

    /**
     * After advice kaydeder.
     *
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Advice metodunun bulunduğu örnek
     * @param \ReflectionMethod $method Advice metodu
     * @param After $attr After attribute
     * @return void
     */
    protected function registerAfterAdvice(AbstractAspect $aspect, object $instance, \ReflectionMethod $method, After $attr): void
    {
        // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
        $pointcut = $this->createPointcutFromExpression($attr->pointcut);

        // After advice oluştur
        $advice = new Advice\AfterAdvice($aspect, $pointcut, [$instance, $method->getName()], $attr->priority);

        // Aspect'e advice ekle (DynamicAspect ise)
        if ($aspect instanceof DynamicAspect) {
            $aspect->addAdvice($advice);
        }
    }

    /**
     * Around advice kaydeder.
     *
     * @param AbstractAspect $aspect Aspect örneği
     * @param object $instance Advice metodunun bulunduğu örnek
     * @param \ReflectionMethod $method Advice metodu
     * @param Around $attr Around attribute
     * @return void
     */
    protected function registerAroundAdvice(AbstractAspect $aspect, object $instance, \ReflectionMethod $method, Around $attr): void
    {
        // Pointcut ifadesine göre MethodPointcut veya AnnotationPointcut oluştur
        $pointcut = $this->createPointcutFromExpression($attr->pointcut);

        // Around advice oluştur
        $advice = new Advice\AroundAdvice($aspect, $pointcut, [$instance, $method->getName()], $attr->priority);

        // Aspect'e advice ekle (DynamicAspect ise)
        if ($aspect instanceof DynamicAspect) {
            $aspect->addAdvice($advice);
        }
    }

    /**
     * Pointcut ifadesinden pointcut nesnesi oluşturur.
     *
     * @param string $expression Pointcut ifadesi
     * @return \Framework\Core\Aspects\Contracts\PointcutInterface Pointcut nesnesi
     */
    protected function createPointcutFromExpression(string $expression): \Framework\Core\Aspects\Contracts\PointcutInterface
    {
        if (str_starts_with($expression, '@')) {
            // Annotation pointcut
            $attributeName = substr($expression, 1);
            return new AnnotationPointcut($attributeName);
        }

        // Method pointcut
        $pointcut = new MethodPointcut('');
        $pointcut->parse($expression);
        return $pointcut;
    }

    /**
     * Aspect sınıfı olmayan bir sınıftaki attribute'lu advice'ları kaydeder.
     *
     * @param \ReflectionClass $reflClass Sınıf yansıması
     * @param object|null $instance Sınıf örneği
     * @return void
     */
    protected function registerClassAdvices(\ReflectionClass $reflClass, ?object $instance): void
    {
        // İmplementasyon özelleştirilebilir
    }

    /**
     * Dosya yolundan sınıf adını çıkarır.
     *
     * @param string $filePath Dosya yolu
     * @param string $namespace Namespace
     * @return string|null Sınıf adı veya null
     */
    protected function getClassNameFromFile(string $filePath, string $namespace): ?string
    {
        // Dosya içeriğini oku
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        // Namespace'i bul
        $namespacePattern = '/namespace\s+([^;]+)/';
        if (preg_match($namespacePattern, $content, $matches)) {
            $fileNamespace = $matches[1];
        } else {
            $fileNamespace = $namespace;
        }

        // Sınıf adını bul
        $classPattern = '/class\s+([^\s{]+)/';
        if (preg_match($classPattern, $content, $matches)) {
            $className = $matches[1];
        } else {
            return null;
        }

        return $fileNamespace ? $fileNamespace . '\\' . $className : $className;
    }
}