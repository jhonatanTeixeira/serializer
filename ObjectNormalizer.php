<?php

namespace Vox\Serializer;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ObjectNormalizer extends AbstractNormalizer
{
    /**
     * @var Normalizer
     */
    private $normalizer;
    
    /**
     * @var Denormalizer
     */
    private $denormalizer;
    
    /**
     * @param \Vox\Serializer\Normalizer $normalizer
     * @param \Vox\Serializer\Denormalizer $denormalizer
     * @param ClassMetadataFactoryInterface $classMetadataFactory
     * @param NameConverterInterface $nameConverter
     */
    public function __construct(
        Normalizer $normalizer,
        Denormalizer $denormalizer,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null
    ) {
        $this->normalizer   = $normalizer;
        $this->denormalizer = $denormalizer;
        
        parent::__construct($classMetadataFactory, $nameConverter);
    }
    
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = $this->changeKeys($data, false);
        
        return $this->denormalizer->denormalize($data, $class, $format, $context);
    }

    public function normalize($object, $format = null, array $context = array())
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        
        return $this->changeKeys($data, true);
    }
    
    private function changeKeys(array $array, bool $direction): array
    {
        $changed = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->changeKeys($value, $direction);
            }
            
            if ($direction) {
                $newKey = $this->nameConverter->normalize($key);
            } else {
                $newKey = $this->nameConverter->denormalize($key);
            }

            $changed[$newKey] = $value;
        }

        return $changed;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->denormalizer->supportsDenormalization($data, $type, $format);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
