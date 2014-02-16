<?php
namespace demo;

// intentional whitespace, because if BeforeMockTransformer thinks it's injecting
// code into TraitedModelTrait, but it actually puts it here, it will end up
// injecting that code into the body of the class, rather than a method, causing
// a parse error (unexpected 'if')







class TraitedClass2 {
    use TraitedClassTrait;

    public function methodInClass() {
        return 1;
    }
}
