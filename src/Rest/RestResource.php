<?php
declare(strict_types = 1);
/**
 * /src/Rest/RestResource.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Rest;

use App\DTO\RestDtoInterface;
use App\Repository\BaseRepositoryInterface;
use BadMethodCallException;
use Doctrine\Common\Proxy\Proxy;
use LogicException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use UnexpectedValueException;
use function array_keys;
use function sprintf;

/**
 * Class RestResource
 *
 * @package App\Rest
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
abstract class RestResource implements RestResourceInterface
{
    // Traits
    use Traits\RestResourceBaseMethods;

    /**
     * @var BaseRepositoryInterface
     */
    private $repository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string
     */
    private $dtoClass;

    /**
     * @var string
     */
    private $formTypeClass;

    /**
     * Getter method for entity repository.
     *
     * @return BaseRepositoryInterface
     */
    public function getRepository(): BaseRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Setter method for repository.
     *
     * @param BaseRepositoryInterface $repository
     *
     * @return RestResourceInterface
     */
    public function setRepository(BaseRepositoryInterface $repository): RestResourceInterface
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Getter for used validator.
     *
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * Setter for used validator.
     *
     * @param ValidatorInterface $validator
     *
     * @return RestResourceInterface
     */
    public function setValidator(ValidatorInterface $validator): RestResourceInterface
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Getter method for used DTO class for this REST service.
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    public function getDtoClass(): string
    {
        if ($this->dtoClass === '') {
            $message = sprintf(
                'DTO class not specified for \'%s\' resource',
                static::class
            );

            throw new UnexpectedValueException($message);
        }

        return $this->dtoClass;
    }

    /**
     * Setter for used DTO class.
     *
     * @param string $dtoClass
     *
     * @return RestResourceInterface
     */
    public function setDtoClass(string $dtoClass): RestResourceInterface
    {
        $this->dtoClass = $dtoClass;

        return $this;
    }

    /**
     * Getter method for used default FormType class for this REST resource.
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    public function getFormTypeClass(): string
    {
        if (!$this->formTypeClass) {
            $message = sprintf(
                'FormType class not specified for \'%s\' resource',
                static::class
            );

            throw new UnexpectedValueException($message);
        }

        return $this->formTypeClass;
    }

    /**
     * Setter method for used default FormType class for this REST resource.
     *
     * @param string $formTypeClass
     *
     * @return RestResourceInterface
     */
    public function setFormTypeClass(string $formTypeClass): RestResourceInterface
    {
        $this->formTypeClass = $formTypeClass;

        return $this;
    }

    /**
     * Getter method for current entity name.
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->getRepository()->getEntityName();
    }

    /** @noinspection GenericObjectTypeUsageInspection */
    /**
     * Gets a reference to the entity identified by the given type and identifier without actually loading it,
     * if the entity is not yet loaded.
     *
     * @param string $id The entity identifier.
     *
     * @return Proxy|object|null
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function getReference(string $id)
    {
        return $this->getRepository()->getReference($id);
    }

    /**
     * Getter method for all associations that current entity contains.
     *
     * @return string[]|array<int, string>|array<int, mixed>
     */
    public function getAssociations(): array
    {
        return array_keys($this->getRepository()->getAssociations());
    }

    /**
     * Getter method DTO class with loaded entity data.
     *
     * @param string                $id
     * @param string                $dtoClass
     * @param RestDtoInterface|null $dto
     *
     * @return RestDtoInterface
     *
     * @throws LogicException
     * @throws BadMethodCallException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getDtoForEntity(string $id, string $dtoClass, ?RestDtoInterface $dto = null): RestDtoInterface
    {
        // Fetch entity
        $entity = $this->getEntity($id);

        // Create new instance of DTO and load entity to that.
        /** @var RestDtoInterface $restDto */
        $restDto = new $dtoClass();
        $restDto->load($entity);

        if ($dto !== null) {
            $restDto->patch($dto);
        }

        return $restDto;
    }
}
