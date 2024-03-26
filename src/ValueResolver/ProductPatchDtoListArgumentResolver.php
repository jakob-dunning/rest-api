<?php

namespace App\ValueResolver;

use App\Dto\ProductPatchDto;
use App\Dto\ProductPatchDtoList;
use App\Library\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductPatchDtoListArgumentResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    /**
     * @return array<ProductPatchDtoList>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType === null) {
            return [];
        }

        if (is_a($argumentType, ProductPatchDtoList::class, true) === false) {
            return [];
        }

        $productPatchDtoList = new ProductPatchDtoList();

        foreach ($request->getPayload()->all() as $patch) {
            $productPatchDtoList->patches[] =
                new ProductPatchDto(
                    Assert::arrayHasPropertyOfTypeString($patch, 'op') ? $patch['op'] : '',
                    Assert::arrayHasPropertyOfTypeString($patch, 'path') ? $patch['path'] : '',
                    Assert::arrayHasPropertyOfTypeStringIntOrNull($patch, 'value') ? $patch['value'] : null,
                    Assert::arrayHasPropertyOfTypeStringOrNull($patch, 'from') ? $patch['from'] : null,
                );
        }

        $constraintViolationList = $this->validator->validate($productPatchDtoList);

        if ($constraintViolationList->count() > 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
            );
        }

        return [$productPatchDtoList];
    }
}
