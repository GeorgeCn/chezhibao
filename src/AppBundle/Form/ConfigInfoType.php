<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * 公司配置表中的基本信息字段
 */
class ConfigInfoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cid', null, array('label' => 'cid'))
            ->add('passToErp', 'choice', array(
                'choices' => array(
                    'true' => '是否转到Erp'
                ),
                'constraints' => new Callback(array($this, 'validate')),
                'label' => false,
                'expanded' => true,
                'multiple' => true,
                'mapped' => true,
            ))
            ->add('ip', null, array('label' => '公司ip', 'attr' => array('placeholder' => '多个ip用英文逗号分开,此配置目前只对平安租赁公司生效')))
        ;
    }

    /**
     * 回调函数验证（根据cid字段是否存在来验证passToErp字段是否可勾选）
     */
    public function validate($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();

        if (!$data->getInfo()['cid'] && $data->getInfo()['passToErp']) {
            $context->buildViolation('当勾选该选项时，cid字段不能为空')
                ->addViolation();
        }
    }
}
