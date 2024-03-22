<?php

namespace App\ValueResolver;

use App\Dto\TabletPatchDto;
use App\Dto\TabletPatchDtoList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TabletPatchDtoListArgumentResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    /**
     * @return array<TabletPatchDtoList>
     */
    #[\Override] public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType === null) {
            return [];
        }

        if (is_a($argumentType, TabletPatchDtoList::class, true) === false) {
            return [];
        }

        $patchDocumentDtoCollection = new TabletPatchDtoList();

        foreach ($request->getPayload()->all() as $patch) {
            try {
                $patchDocumentDtoCollection->patches[] =
                    new TabletPatchDto(
                        $patch['op'],
                        $patch['path'],
                        $patch['value'] ?? null,
                        $patch['from'] ?? null,
                    );
            } catch (\Throwable $t) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    '',
                    new ValidationFailedException(
                        $patch,
                        new ConstraintViolationList([new ConstraintViolation(
                            message: 'Invalid patch options',
                            messageTemplate: null,
                            parameters: [],
                            root: $patch,
                            propertyPath: null,
                            invalidValue: $patch,
                        )])
                    )
                );
            }
        }

        $constraintViolationList = $this->validator->validate($patchDocumentDtoCollection);

        if ($constraintViolationList->count() > 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
            );
        }

        return [$patchDocumentDtoCollection];
    }
}
