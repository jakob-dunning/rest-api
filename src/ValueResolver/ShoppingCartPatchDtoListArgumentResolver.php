<?php

namespace App\ValueResolver;

use App\Dto\ShoppingCartPatchDto;
use App\Dto\ShoppingCartPatchDtoList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShoppingCartPatchDtoListArgumentResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    /**
     * @return iterable<ShoppingCartPatchDtoList>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType === null) {
            return [];
        }

        if (is_a($argumentType, ShoppingCartPatchDtoList::class, true) === false) {
            return [];
        }

        $shoppingCartPatchDtoList = new ShoppingCartPatchDtoList();

        foreach ($request->getPayload()->all() as $patch) {
            $shoppingCartPatchDtoList->patches[] =
                new ShoppingCartPatchDto(
                    $patch['op'] ?? '',
                    $patch['path'] ?? '',
                    $patch['value'] ?? null,
                    $patch['from'] ?? null,
                );
        }

        $constraintViolationList = $this->validator->validate($shoppingCartPatchDtoList);

        if ($constraintViolationList->count() > 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
            );
        }

        return [$shoppingCartPatchDtoList];
    }
}
