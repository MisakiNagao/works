<?php
// 引数の暗号を複合化する
function decrypt($forDec){

    //キー（任意の文字列）
    $key = '
    0APS5Yh8jL9vIyWelYiypXKtYgFD267MbScQCggbXDEQE+5X/sC4AI/ss7pabqJh
    z99iUWa8kIaOakQtvRBkF2f0MwGolwJe56N/y472hgeXo60uoiKIO3nmksg0DrXl
    pUoHbREwOo8oc2fcH32QICIw6TqEGs4MiHjkKJh0YyVp3xjyXNBoJ8gJJKWw6Pl6
    sm/y4kMWZey9bfIbqmkCVCGUCxtjdxMA3IjJeCiD4DxxNErvZAXNUnpQtQJ72FaH
    SozwRKcUDYHgUo8Txilbvqfx+fhdpSMrnvAxDE0DGujiQoru6mLKvkxzKgx7mbUr
    2e/h9zvqw91dwU3H+rbbxtMCX0QINhHV5R2jIjnxAc3dQ2umPOXYZzJkOUDpPhnk
    tUfwsllyilR8CAv36pcllKzeC1BDylg4kn7b2b2tmVP8ToCj8Wjl+k9qc9GCj6LO
    QXFiHYWnXur8ihDZp+CC3DGYJDIA+Zm82Sq+s24jUoMcfMksBbcrtYWXWtf+O7yI
    gLfpzxjMaDgM26pJoHb5/NMDv7yWaztuHBOB3saukT/yA+pcUEZIyfBbW+0k8rvA
    E+xzm3Q06n/TTpBPx8Vt2Blc7YPVkj+4+U1HjydKCh/Fg2I8YTjgk7DaH9iQdzv0
    mtGGNSCknjBL9L2w4fAsTmveX/oFBxw6CG70X0HkO9zXyIQYOTd6fwSuRvHSRInW
    w/pqL/wOqpcVD6kPdSSCSwecEXEYiU9fm/BJJhc8pp59/jOmIZkumWccOdcBnQfM
    Bezh8WfKWEKm4+o3TFgKHkWjwsjDZq4810713vQMaByJdtfF4bc8OrUTVbEbaVsD
    e73RnfPrnDelkiqk0DpRM3pSZqMB/oUJSYsp4W6UaKNPcatGzwsWruvkiYLaZQRv
    GO09HAGSeDUwFRlOJ3X1amg4Vkgte55Eo6ojnvkb2VqweIzzDpn2OrFbsl9IQ41w
    PLaMX0TueDOrRvYqd0tufAo64h1MgY/R6lmqcFd4/nIw1M/TWgPwioTSPd9YSYEk
    IVq1QEJFAtRyvzk0Zg+7p2GBrPcSydHi3/Es/Vf0/KXSa9aBD7Ogeqzl8OorrRdn
    KEWw63TnqjgXQYyRTywxAOmdWuydE0RUaM6p8es99IOUQZGDpC3HZMWU+4Ab5Ny3
    JA8+/Q37iTIvZm5ohjuRHPfXb5FHLNIHYZcJDLtJdxpfZMK+y5tfZNvMt0ZyntKP
    kLstbSYzV3uJBFTzGnDXh0T0YrouG62gonyM7pZ1BSTJNUhwGjmAQloZRCrEseI2
    yEhrqp2K6wIWdhKEJxsTJDzM40qzmTPm9p3JKXD1+DPf4RzOXzJX7zoSvpeC33ad
    oQ50K61IhfseShdx+dysHUfAiKH1AS3aFV07inkBPochUFHMnxuIeMKfNQRctcZB
    JxExlgyvdXy0uL1XjhCiGNeQvMDZ6RmkfY/Hps1H26qSs7lU/pFkGENJNj9H+XBD
    B7ceuKJLR7d7ahe2pUlows+tH65rgf6UqPvTRvZzeJbWa19Q1tqTksGR/RPLbXN1
    b4Ak5TObm8WF8uA5EprePAaVn+ZtGdkQKKGRymxnwConwKw+L8ScmGjXGjlI+IkG
    ';
    
    //暗号化メソッド
    $method = 'AES-128-CBC';
    
    //復号化
    $decrypt = openssl_decrypt($forDec, $method, $key);

    return $decrypt;
}
?>